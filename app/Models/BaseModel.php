<?php

namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    protected $useSoftDeletes = false;
    protected $deletedField = 'deleted_at';
    
    protected function sanitizeInput($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = trim($value);
            }
        }
        
        return $data;
    }
    
    public function findWithRelations($id, array $relations = [])
    {
        $query = $this;
        
        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $query = $this->$relation();
            }
        }
        
        return $query->find($id);
    }
    
    public function paginate($perPage = 20, string $group = 'default', int $page = null, int $segment = 2)
    {
        return parent::paginate($perPage, $group, $page, $segment);
    }
}