<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        $data = [
            'users' => $this->userModel->findAll(),
            'title' => 'Manage Users'
        ];
        
        return view('admin/users/index', $data);
    }

    public function show($id = null)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }
        
        $data = [
            'user' => $user,
            'title' => 'View User'
        ];
        
        return view('admin/users/show', $data);
    }

    public function new()
    {
        $data = [
            'title' => 'Create New User',
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/users/create', $data);
    }

    public function create()
    {
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'password_confirm' => 'required|matches[password]',
            'role' => 'required|in_list[admin,instructor,student]'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role' => $this->request->getPost('role'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0,
            'email_verified_at' => date('Y-m-d H:i:s')
        ];

        if ($this->userModel->save($userData)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User created successfully.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create user.');
        }
    }

    public function edit($id = null)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }
        
        $data = [
            'user' => $user,
            'title' => 'Edit User',
            'validation' => \Config\Services::validation()
        ];
        
        return view('admin/users/edit', $data);
    }

    public function update($id = null)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role' => 'required|in_list[admin,instructor,student]'
        ];

        // Only validate password if provided
        if ($this->request->getPost('password')) {
            $rules['password'] = 'required|min_length[8]';
            $rules['password_confirm'] = 'required|matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('validation', $this->validator);
        }

        $userData = [
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role' => $this->request->getPost('role'),
            'is_active' => $this->request->getPost('is_active') ? 1 : 0
        ];

        // Only update password if provided
        if ($this->request->getPost('password')) {
            $userData['password'] = $this->request->getPost('password');
        }

        if ($this->userModel->update($id, $userData)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User updated successfully.');
        } else {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update user.');
        }
    }

    public function delete($id = null)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('User not found');
        }

        // Prevent admin from deleting themselves
        if ($user['id'] == session()->get('user_id')) {
            return redirect()->to('/admin/users')
                ->with('error', 'You cannot delete your own account.');
        }

        if ($this->userModel->delete($id)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User deleted successfully.');
        } else {
            return redirect()->to('/admin/users')
                ->with('error', 'Failed to delete user.');
        }
    }

    public function activate($id = null)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')
                ->with('error', 'User not found.');
        }

        if ($this->userModel->activateUser($id)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User activated successfully.');
        } else {
            return redirect()->to('/admin/users')
                ->with('error', 'Failed to activate user.');
        }
    }

    public function deactivate($id = null)
    {
        $user = $this->userModel->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/users')
                ->with('error', 'User not found.');
        }

        // Prevent admin from deactivating themselves
        if ($user['id'] == session()->get('user_id')) {
            return redirect()->to('/admin/users')
                ->with('error', 'You cannot deactivate your own account.');
        }

        if ($this->userModel->deactivateUser($id)) {
            return redirect()->to('/admin/users')
                ->with('success', 'User deactivated successfully.');
        } else {
            return redirect()->to('/admin/users')
                ->with('error', 'Failed to deactivate user.');
        }
    }
}