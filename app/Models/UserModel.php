<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends BaseModel
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name',
        'email', 
        'password',
        'role',
        'moodle_id',
        'is_active',
        'email_verified_at',
        'remember_token'
    ];
    
    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[255]',
        'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
        'password' => 'required|min_length[8]',
        'role' => 'in_list[admin,instructor,student]'
    ];
    
    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email is already registered.',
            'valid_email' => 'Please provide a valid email address.'
        ],
        'password' => [
            'min_length' => 'Password must be at least 8 characters long.'
        ]
    ];
    
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];
    
    protected function hashPassword(array $data)
    {
        if (!isset($data['data']['password'])) {
            return $data;
        }
        
        $data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
        
        return $data;
    }
    
    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }
    
    public function verifyPassword($email, $password)
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (!password_verify($password, $user['password'])) {
            return false;
        }
        
        return $user;
    }
    
    public function updateRememberToken($userId, $token = null)
    {
        return $this->update($userId, ['remember_token' => $token]);
    }
    
    public function findByRememberToken($token)
    {
        if (!$token) {
            return null;
        }
        
        return $this->where('remember_token', $token)->first();
    }
    
    public function getActiveUsers()
    {
        return $this->where('is_active', 1)->findAll();
    }
    
    public function getUsersByRole($role)
    {
        return $this->where('role', $role)->findAll();
    }
    
    public function activateUser($userId)
    {
        return $this->update($userId, [
            'is_active' => true,
            'email_verified_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function deactivateUser($userId)
    {
        return $this->update($userId, ['is_active' => false]);
    }
    
    public function isAdmin($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'admin';
    }
    
    public function isInstructor($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'instructor';
    }
    
    public function isStudent($userId)
    {
        $user = $this->find($userId);
        return $user && $user['role'] === 'student';
    }
}