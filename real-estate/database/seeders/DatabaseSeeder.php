<?php

namespace Database\Seeders;

use App\Models\Agent;
use App\Models\City;
use App\Models\Contact;
use App\Models\District;
use App\Models\Favorite;
use App\Models\Property;
use App\Models\PropertyImage;
use App\Models\PropertyType;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // 2. Create Regular Users
        $users = User::factory(10)->create();

        // 3. Create Property Types
        $propertyTypes = [
            ['name' => 'House', 'slug' => 'house'],
            ['name' => 'Apartment', 'slug' => 'apartment'],
            ['name' => 'Villa', 'slug' => 'villa'],
            ['name' => 'Office', 'slug' => 'office'],
            ['name' => 'Land', 'slug' => 'land'],
        ];

        foreach ($propertyTypes as $type) {
            PropertyType::create($type);
        }

        // 4. Create Cities and Districts
        $citiesData = [
            ['name' => 'Ho Chi Minh City', 'slug' => 'ho-chi-minh-city', 'state' => 'HCMC', 'country' => 'Vietnam'],
            ['name' => 'Ha Noi', 'slug' => 'ha-noi', 'state' => 'HN', 'country' => 'Vietnam'],
            ['name' => 'Da Nang', 'slug' => 'da-nang', 'state' => 'DN', 'country' => 'Vietnam'],
        ];

        foreach ($citiesData as $cityData) {
            $city = City::create($cityData);
            District::factory(5)->create(['city_id' => $city->id]);
        }

        // 5. Create Agents
        $agents = Agent::factory(5)->create();

        // 6. Create Properties
        $allPropertyTypes = PropertyType::all();
        $allCities = City::all();

        foreach ($agents as $agent) {
            $properties = Property::factory(3)->create([
                'user_id' => $agent->user_id,
                'agent_id' => $agent->id,
                'property_type_id' => $allPropertyTypes->random()->id,
                'city_id' => $allCities->random()->id,
                'district_id' => function (array $attributes) {
                    return District::where('city_id', $attributes['city_id'])->get()->random()->id;
                },
            ]);

            foreach ($properties as $property) {
                // Add Images
                PropertyImage::factory(3)->create(['property_id' => $property->id]);

                // Add Reviews
                Review::factory(2)->create([
                    'property_id' => $property->id,
                    'user_id' => $users->random()->id,
                    'agent_id' => $agent->id,
                ]);

                // Add Favorites
                Favorite::create([
                    'user_id' => $users->random()->id,
                    'property_id' => $property->id,
                ]);

                // Add Contacts
                Contact::factory(2)->create([
                    'property_id' => $property->id,
                    'user_id' => $users->random()->id,
                    'agent_id' => $agent->id,
                ]);
            }
        }
    }
}
