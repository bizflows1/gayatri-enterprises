<?php

namespace Database\Seeders;

use App\Models\WebsiteTeamMember;
use Illuminate\Database\Seeder;

class WebsiteTeamMemberSeeder extends Seeder
{
    public function run(): void
    {
        $members = [
            [
                'name'          => 'Rakesh Sharma',
                'role'          => 'Managing Director',
                'bio'           => 'Over 25 years of experience in chemical procurement and supply chain management.',
                'image_path'    => '/images/member_md.jpeg',
                'category'      => 'partner',
                'display_order' => 1,
            ],
            [
                'name'          => 'Dr. Anjali Desai',
                'role'          => 'Technical Lead',
                'bio'           => 'Ph.D. in Analytical Chemistry, ensuring quality control and client technical support.',
                'image_path'    => '/images/member_tech.jpeg',
                'category'      => 'partner',
                'display_order' => 2,
            ],
            [
                'name'          => 'Vikram Singh',
                'role'          => 'Head of Logistics',
                'image_path'    => '/images/member_logistics.jpeg',
                'category'      => 'associate',
                'display_order' => 3,
            ],
            [
                'name'          => 'Priya Patel',
                'role'          => 'Client Relations Manager',
                'image_path'    => '/images/member_relations.jpeg',
                'category'      => 'associate',
                'display_order' => 4,
            ],
            [
                'name'          => 'Arun Kumar',
                'role'          => 'Procurement Specialist',
                'image_path'    => '/images/member_procure.jpeg',
                'category'      => 'associate',
                'display_order' => 5,
            ],
            [
                'name'          => 'Meena Reddy',
                'role'          => 'Quality Assurance Officer',
                'image_path'    => '/images/member_qa.jpeg',
                'category'      => 'associate',
                'display_order' => 6,
            ],
        ];

        foreach ($members as $member) {
            WebsiteTeamMember::firstOrCreate(['name' => $member['name']], $member);
        }
    }
}
