<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProjectListingTest extends TestCase
{
    public function test_projects_endpoint_returns_portfolio_projects_and_verified_impact_details(): void
    {
        $response = $this->getJson('/api/projects');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Assets Inventory Management System (AIMS)')
            ->assertJsonPath('data.0.type', 'Private Project · Asset management')
            ->assertJsonPath('data.0.client', 'Poro Point Management Corporation')
            ->assertJsonPath('data.0.year', '2025')
            ->assertJsonPath('data.0.tags', ['Laravel 11', 'PHP 8.2', 'MySQL', 'Bootstrap'])
            ->assertJsonPath('data.1.title', 'Energy Resiliency Management System (ERMS)')
            ->assertJsonPath('data.1.type', 'Government Project')
            ->assertJsonPath('data.1.client', 'Department of Energy')
            ->assertJsonPath('data.1.year', '2025')
            ->assertJsonPath('data.1.tags', ['Vue 3', 'TypeScript', 'Laravel', 'MySQL'])
            ->assertJsonPath('data.1.preview', 'erms')
            ->assertJsonPath('data.1.features', [
                'Developed responsive dashboards for different user roles.',
                'Implemented facility and operator management.',
                'Developed RCP submission and approval workflows.',
                'Integrated REST APIs between Vue and Laravel.',
                'Implemented role-based access and data filtering.',
            ]);
    }
}
