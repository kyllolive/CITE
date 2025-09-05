<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CourseModel;
use App\Models\UserModel;

class CourseController extends BaseController
{
    protected $courseModel;
    protected $userModel;

    public function __construct()
    {
        $this->courseModel = new CourseModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $courses = $this->courseModel->select('courses.*, users.name as instructor_name')
                                    ->join('users', 'users.id = courses.instructor_id', 'left')
                                    ->findAll();
        
        $data = [
            'courses' => $courses,
            'title' => 'Manage Courses'
        ];
        
        return view('admin/courses/index', $data);
    }

    public function show($id = null)
    {
        $course = $this->courseModel->getWithInstructor($id);
        
        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found');
        }
        
        $enrolledStudents = $this->courseModel->getEnrolledStudents($id);
        $enrollmentCount = $this->courseModel->getEnrollmentCount($id);
        
        $data = [
            'course' => $course,
            'enrolledStudents' => $enrolledStudents,
            'enrollmentCount' => $enrollmentCount,
            'title' => 'View Course'
        ];
        
        return view('admin/courses/show', $data);
    }

    public function new()
    {
        $instructors = $this->userModel->where('role', 'instructor')
                                     ->where('is_active', 1)
                                     ->findAll();
        
        $data = [
            'instructors' => $instructors,
            'title' => 'Create New Course',
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/courses/create', $data);
    }

    public function create()
    {
        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'required',
            'instructor_id' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[active,inactive,archived]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date' => 'permit_empty|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $courseData = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'instructor_id' => $this->request->getPost('instructor_id'),
            'status' => $this->request->getPost('status'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
            'moodle_id' => $this->request->getPost('moodle_id') ?: null
        ];

        if ($this->courseModel->save($courseData)) {
            return redirect()->to('/admin/courses')
                ->with('success', 'Course created successfully.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create course.');
        }
    }

    public function edit($id = null)
    {
        $course = $this->courseModel->find($id);
        
        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found');
        }
        
        $instructors = $this->userModel->where('role', 'instructor')
                                     ->where('is_active', 1)
                                     ->findAll();
        
        $data = [
            'course' => $course,
            'instructors' => $instructors,
            'title' => 'Edit Course',
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/courses/edit', $data);
    }

    public function update($id = null)
    {
        $course = $this->courseModel->find($id);
        
        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[255]',
            'description' => 'required',
            'instructor_id' => 'required|is_natural_no_zero',
            'status' => 'required|in_list[active,inactive,archived]',
            'start_date' => 'permit_empty|valid_date[Y-m-d]',
            'end_date' => 'permit_empty|valid_date[Y-m-d]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $courseData = [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'instructor_id' => $this->request->getPost('instructor_id'),
            'status' => $this->request->getPost('status'),
            'start_date' => $this->request->getPost('start_date') ?: null,
            'end_date' => $this->request->getPost('end_date') ?: null,
            'moodle_id' => $this->request->getPost('moodle_id') ?: null
        ];

        if ($this->courseModel->update($id, $courseData)) {
            return redirect()->to('/admin/courses')
                ->with('success', 'Course updated successfully.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update course.');
        }
    }

    public function delete($id = null)
    {
        $course = $this->courseModel->find($id);
        
        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found');
        }

        $enrollmentCount = $this->courseModel->getEnrollmentCount($id);
        
        if ($enrollmentCount > 0) {
            return redirect()->to('/admin/courses')
                ->with('error', 'Cannot delete course with active enrollments. Please unenroll all students first.');
        }

        if ($this->courseModel->delete($id)) {
            return redirect()->to('/admin/courses')
                ->with('success', 'Course deleted successfully.');
        } else {
            return redirect()->to('/admin/courses')
                ->with('error', 'Failed to delete course.');
        }
    }

    public function activate($id = null)
    {
        $course = $this->courseModel->find($id);
        
        if (!$course) {
            return redirect()->to('/admin/courses')
                ->with('error', 'Course not found.');
        }

        if ($this->courseModel->update($id, ['status' => 'active'])) {
            return redirect()->to('/admin/courses')
                ->with('success', 'Course activated successfully.');
        } else {
            return redirect()->to('/admin/courses')
                ->with('error', 'Failed to activate course.');
        }
    }

    public function archive($id = null)
    {
        $course = $this->courseModel->find($id);
        
        if (!$course) {
            return redirect()->to('/admin/courses')
                ->with('error', 'Course not found.');
        }

        if ($this->courseModel->update($id, ['status' => 'archived'])) {
            return redirect()->to('/admin/courses')
                ->with('success', 'Course archived successfully.');
        } else {
            return redirect()->to('/admin/courses')
                ->with('error', 'Failed to archive course.');
        }
    }

    public function enrollments($id = null)
    {
        $course = $this->courseModel->getWithInstructor($id);
        
        if (!$course) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Course not found');
        }
        
        $enrolledStudents = $this->courseModel->getEnrolledStudents($id);
        $availableStudents = $this->userModel->select('users.*')
                                           ->where('role', 'student')
                                           ->where('is_active', 1)
                                           ->whereNotIn('id', array_column($enrolledStudents, 'id'))
                                           ->findAll();
        
        $data = [
            'course' => $course,
            'enrolledStudents' => $enrolledStudents,
            'availableStudents' => $availableStudents,
            'title' => 'Manage Enrollments - ' . $course['title']
        ];
        
        return view('admin/courses/enrollments', $data);
    }

    public function enroll($courseId = null)
    {
        $studentId = $this->request->getPost('student_id');
        
        if (!$courseId || !$studentId) {
            return redirect()->back()
                ->with('error', 'Invalid course or student ID.');
        }
        
        if ($this->courseModel->isStudentEnrolled($courseId, $studentId)) {
            return redirect()->back()
                ->with('error', 'Student is already enrolled in this course.');
        }
        
        if ($this->courseModel->enrollStudent($courseId, $studentId)) {
            return redirect()->back()
                ->with('success', 'Student enrolled successfully.');
        } else {
            return redirect()->back()
                ->with('error', 'Failed to enroll student.');
        }
    }

    public function unenroll($courseId = null, $studentId = null)
    {
        if (!$courseId || !$studentId) {
            return redirect()->back()
                ->with('error', 'Invalid course or student ID.');
        }
        
        if ($this->courseModel->unenrollStudent($courseId, $studentId)) {
            return redirect()->back()
                ->with('success', 'Student unenrolled successfully.');
        } else {
            return redirect()->back()
                ->with('error', 'Failed to unenroll student.');
        }
    }
}