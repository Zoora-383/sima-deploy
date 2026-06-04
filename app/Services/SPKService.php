<?php

namespace App\Services;

use Exception;

class SPKService
{
    public function addSPK()
    {
        try {
            

        } catch (Exception $e) {
            throw new Exception("Failed to update role: " . $e->getMessage());
        }
    }
}
