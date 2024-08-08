<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Category::count() == 0) {
            Category::insert([
                [
                    'name' => 'Uncategorized Item',
                    'slug' => 'uncategorizeditem'
                ],
                [
                    'name' => 'Freegift',
                    'slug' => 'freegift'
                ],
                [
                    'name' => 'Services, Labor Charge',
                    'slug' => 'other_services'
                ],
                [
                    'name' => 'Car Oils & Fluids',
                    'slug' => 'car_oils_fluids'
                ],
                [
                    'name' => 'AC Compressor, Condenser & Climate Control',
                    'slug' => 'aircond_system'
                ],
                [
                    'name' => 'Air Filters & Intake System Parts',
                    'slug' => 'air_intake_system'
                ],
                [
                    'name' => 'Alternator, Starter, Tune Up & Engine Electrical',
                    'slug' => 'engine_electrical'
                ],
                [
                    'name' => 'Brake Pads, Rotors, Calipers & Brake Parts',
                    'slug' => 'brake_system'
                ],
                [
                    'name' => 'Carburetor Parts & Components',
                    'slug' => 'carburetor_system'
                ],
                [
                    'name' => 'Clutch, Flywheel, Flex Plate & Clutch Parts',
                    'slug' => 'clutch_system'
                ],
                [
                    'name' => 'Diesel Fuel Injectors & Injection Parts',
                    'slug' => 'diesel_injection_system'
                ],
                [
                    'name' => 'Drive Belts, Serpentine Belts & Components',
                    'slug' => 'belts_system'
                ],
                [
                    'name' => 'Fuel Pump, Filters, Tanks, Caps & Fuel Delivery',
                    'slug' => 'fuel_delivery_system'
                ],
                [
                    'name' => 'Headlights, Tail Lights & Body Electrical Parts',
                    'slug' => 'body_electrical'
                ],
                [
                    'name' => 'Injectors, Oxygen Sensors, Throttle Body & Fuel Injection',
                    'slug' => 'injection_system'
                ],
                [
                    'name' => 'Mirrors, Grilles, Body Mechanical & Trim',
                    'slug' => 'body_mechanical'
                ],
                [
                    'name' => 'Mufflers, Exhaust Manifold & Exhaust System',
                    'slug' => 'exhaust_system'
                ],
                [
                    'name' => 'Radiator, Fan, Thermostat, Water Pump & Cooling System',
                    'slug' => 'cooling_system'
                ],
                [
                    'name' => 'Shocks, Struts, Control Arm, Ball Joint & Suspension',
                    'slug' => 'suspension_system'
                ],
                [
                    'name' => 'Steering Rack, Pumps & Steering Parts',
                    'slug' => 'steering_system'
                ],
                [
                    'name' => 'Timing Belts, Chains, Head Gaskets & Engine Mechanical',
                    'slug' => 'engine_mechanical'
                ],
                [
                    'name' => 'Transmission Filter, Mounts & Transmission Parts',
                    'slug' => 'transmission_system'
                ],
                [
                    'name' => 'Wheel Bearings, Wheels, Driveshaft & Axle',
                    'slug' => 'wheel_system'
                ],
                [
                    'name' => 'Other miscellaneous parts',
                    'slug' => 'other_miscellaneous_parts'
                ],
                [
                    'name' => 'Aksesori',
                    'slug' => 'aksesori'
                ]
            ]);
        } else {
            echo "\e[31mTable is not empty, therefore NOT ";
        }
        
    }
}
