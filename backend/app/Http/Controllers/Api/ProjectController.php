<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                [
                    'title' => 'Assets Inventory Management System (AIMS)',
                    'category' => 'Full-stack',
                    'type' => 'Private Project · Asset management',
                    'client' => 'Poro Point Management Corporation',
                    'year' => '2025',
                    'description' => 'A web-based asset management platform developed for Poro Point Management Corporation (PPMC) to centralize asset records, monitor transactions, and manage inventory operations. It streamlines asset tracking, Gate Pass processing, and property accountability through organized digital workflows.',
                    'problem' => 'Centralize asset records and improve visibility into inventory, transactions, Gate Pass processing, and property accountability.',
                    'role' => 'Contributed to full-stack asset management features and transaction workflows, implemented Gate Pass management, supported PAR and ICS management, integrated authentication and two-factor authentication, and contributed to technical documentation.',
                    'features' => [
                        'Centralized asset inventory and transaction workflows.',
                        'Gate Pass processing and management.',
                        'Property accountability through PAR and ICS management.',
                        'Authentication with two-factor authentication.',
                        'Technical documentation for system workflows.',
                    ],
                    'impact' => [],
                    'tags' => ['Laravel 11', 'PHP 8.2', 'MySQL', 'Bootstrap'],
                    'repository' => null,
                    'demo' => null,
                    'accent' => 'blue',
                    'preview' => 'aims',
                ],
                [
                    'title' => 'Energy Resiliency Management System (ERMS)',
                    'category' => 'Full-stack',
                    'type' => 'Government Project',
                    'client' => 'Department of Energy',
                    'year' => '2025',
                    'description' => 'A web-based energy resiliency management platform designed to streamline facility management, operator administration, and Resiliency Compliance Plan (RCP) submissions. It provides centralized monitoring, validation, and reporting workflows to improve operational efficiency, disaster preparedness, and regulatory compliance.',
                    'problem' => 'Coordinate facility information, operator administration, and resiliency plan reviews across multiple user roles.',
                    'role' => 'Contributed to responsive frontend and backend workflows for role-based dashboards, facility and operator management, and RCP submissions.',
                    'features' => [
                        'Developed responsive dashboards for different user roles.',
                        'Implemented facility and operator management.',
                        'Developed RCP submission and approval workflows.',
                        'Integrated REST APIs between Vue and Laravel.',
                        'Implemented role-based access and data filtering.',
                    ],
                    'impact' => [],
                    'tags' => ['Vue 3', 'TypeScript', 'Laravel', 'MySQL'],
                    'repository' => null,
                    'demo' => null,
                    'accent' => 'blue',
                    'preview' => 'erms',
                ],
            ],
        ]);
    }
}
