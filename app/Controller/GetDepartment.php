<?php

namespace App\Controller;

use App\Model\Departement;

class GetDepartment {

    protected array $departments = [];

    /**
     * Récupère tous les départements triés par nom
     * 
     * @return array
     */
    public function getAllDepartments(): array
    {
        return Departement::orderBy('nom_departement')->get()->toArray();
    }
}
