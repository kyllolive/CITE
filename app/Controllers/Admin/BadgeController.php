<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\Badge;
use App\Models\BadgeAward;
use App\Models\CourseModel;
use App\Libraries\MoodleAPI;

class BadgeController extends BaseController
{
    protected $badgeModel;
    protected $badgeAwardModel;
    protected $courseModel;
    protected $moodleApi;

    public function __construct()
    {
        $this->badgeModel = new Badge();
        $this->badgeAwardModel = new BadgeAward();
        $this->courseModel = new CourseModel();
        $this->moodleApi = new MoodleAPI();
    }

    public function index()
    {
        $data = [
            'badges' => $this->badgeModel->getBadgesWithCourse(),
            'title' => 'Manage Badges'
        ];
        
        return view('admin/badges/index', $data);
    }

    public function show($id = null)
    {
        $badge = $this->badgeModel->getBadgeWithDetails($id);
        
        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found');
        }

        $data = [
            'badge' => $badge,
            'awards' => $this->badgeAwardModel->getBadgeAwards($id),
            'awardCount' => $this->badgeModel->getAwardCount($id),
            'title' => 'View Badge'
        ];
        
        return view('admin/badges/show', $data);
    }

    public function new()
    {
        $data = [
            'title' => 'Create New Badge',
            'courses' => $this->courseModel->getActiveCourses(),
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/badges/create', $data);
    }

    public function create()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'required',
            'course_id' => 'required|is_natural_no_zero',
            'criteria' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $badgeData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'course_id' => $this->request->getPost('course_id'),
            'criteria' => $this->request->getPost('criteria'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'created_by' => session()->get('user_id')
        ];

        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newName = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/badges', $newName);
            $badgeData['image_url'] = '/uploads/badges/' . $newName;
        }

        if ($this->badgeModel->save($badgeData)) {
            $badgeId = $this->badgeModel->getInsertID();
            
            // Create badge in Moodle if sync is enabled
            if (getenv('MOODLE_SYNC_ENABLED') === 'true') {
                try {
                    $this->syncBadgeToMoodle($badgeId, $badgeData);
                } catch (\Exception $e) {
                    log_message('warning', 'Moodle badge sync failed but badge created in CITE: ' . $e->getMessage());
                }
            }
            
            return redirect()->to('/admin/badges')
                ->with('success', 'Badge created successfully.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create badge.');
        }
    }

    public function edit($id = null)
    {
        $badge = $this->badgeModel->find($id);
        
        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found');
        }
        
        $data = [
            'badge' => $badge,
            'courses' => $this->courseModel->getActiveCourses(),
            'title' => 'Edit Badge',
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/badges/edit', $data);
    }

    public function update($id = null)
    {
        $badge = $this->badgeModel->find($id);
        
        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'description' => 'required',
            'course_id' => 'required|is_natural_no_zero',
            'criteria' => 'permit_empty|string'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $badgeData = [
            'name' => $this->request->getPost('name'),
            'description' => $this->request->getPost('description'),
            'course_id' => $this->request->getPost('course_id'),
            'criteria' => $this->request->getPost('criteria'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        // Handle image upload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Delete old image if exists
            if ($badge['image_url'] && file_exists(ROOTPATH . 'public' . $badge['image_url'])) {
                unlink(ROOTPATH . 'public' . $badge['image_url']);
            }
            
            $newName = $image->getRandomName();
            $image->move(ROOTPATH . 'public/uploads/badges', $newName);
            $badgeData['image_url'] = '/uploads/badges/' . $newName;
        }

        if ($this->badgeModel->update($id, $badgeData)) {
            return redirect()->to('/admin/badges')
                ->with('success', 'Badge updated successfully.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update badge.');
        }
    }

    public function delete($id = null)
    {
        $badge = $this->badgeModel->find($id);
        
        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found');
        }

        // Check if badge has awards
        if ($this->badgeModel->getAwardCount($id) > 0) {
            return redirect()->to('/admin/badges')
                ->with('error', 'Cannot delete badge that has been awarded to users.');
        }

        // Delete image if exists
        if ($badge['image_url'] && file_exists(ROOTPATH . 'public' . $badge['image_url'])) {
            unlink(ROOTPATH . 'public' . $badge['image_url']);
        }

        if ($this->badgeModel->delete($id)) {
            return redirect()->to('/admin/badges')
                ->with('success', 'Badge deleted successfully.');
        } else {
            return redirect()->to('/admin/badges')
                ->with('error', 'Failed to delete badge.');
        }
    }

    public function activate($id = null)
    {
        $badge = $this->badgeModel->find($id);
        
        if (!$badge) {
            return redirect()->to('/admin/badges')
                ->with('error', 'Badge not found.');
        }

        if ($this->badgeModel->activateBadge($id)) {
            return redirect()->to('/admin/badges')
                ->with('success', 'Badge activated successfully.');
        } else {
            return redirect()->to('/admin/badges')
                ->with('error', 'Failed to activate badge.');
        }
    }

    public function deactivate($id = null)
    {
        $badge = $this->badgeModel->find($id);
        
        if (!$badge) {
            return redirect()->to('/admin/badges')
                ->with('error', 'Badge not found.');
        }

        if ($this->badgeModel->deactivateBadge($id)) {
            return redirect()->to('/admin/badges')
                ->with('success', 'Badge deactivated successfully.');
        } else {
            return redirect()->to('/admin/badges')
                ->with('error', 'Failed to deactivate badge.');
        }
    }

    public function awards($id = null)
    {
        $badge = $this->badgeModel->getBadgeWithDetails($id);
        
        if (!$badge) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Badge not found');
        }

        $data = [
            'badge' => $badge,
            'awards' => $this->badgeAwardModel->getBadgeAwards($id),
            'title' => 'Badge Awards'
        ];
        
        return view('admin/badges/awards', $data);
    }

    protected function syncBadgeToMoodle($badgeId, $badgeData)
    {
        try {
            $course = $this->courseModel->find($badgeData['course_id']);
            if (!$course || !$course['moodle_id']) {
                return false;
            }

            // Create badge in Moodle via API call
            $moodleBadgeData = [
                'name' => $badgeData['name'],
                'description' => $badgeData['description'],
                'courseid' => $course['moodle_id'],
                'imagedata' => $badgeData['image_url'] ?? '',
            ];

            $result = $this->moodleApi->call('local_citebadges_create_course_badge', $moodleBadgeData);

            if ($result && isset($result['badgeid'])) {
                $this->badgeModel->syncWithMoodle($badgeId, $result['badgeid']);
                return true;
            }
        } catch (\Exception $e) {
            log_message('error', 'Failed to sync badge to Moodle: ' . $e->getMessage());
        }

        return false;
    }
}