<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

      $this->call(LaratrustSeeder::class);
      $this->call(UsersTableSeeder::class);
      $this->call(DepartmentSeeder::class);
      $this->call(AreaSeeder::class);
      $this->call(PlantSeeder::class);
      $this->call(SectorSeeder::class);
      $this->call(ArticleTypeSeeder::class);
      $this->call(ArticleSeeder::class);
      $this->call(FileSeeder::class);
      $this->call(IerSeeder::class);

      
      
    }
}
