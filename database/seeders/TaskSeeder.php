<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $tasks = [
            // SPORT - INDIVIDUAL - EASY
            ['title' => '20-minute walk', 'description' => 'Take a relaxing 20-minute walk outside.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 10, 'is_active' => true],
            ['title' => '10-minute stretching', 'description' => 'Do a full body stretching routine for 10 minutes.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 10, 'is_active' => true],
            ['title' => '15-minute yoga', 'description' => 'Follow a beginner yoga routine for 15 minutes.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 15, 'is_active' => true],

            // SPORT - INDIVIDUAL - MEDIUM
            ['title' => '30-minute run', 'description' => 'Go for a 30-minute jog at a comfortable pace.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 25, 'is_active' => true],
            ['title' => '45-minute gym session', 'description' => 'Complete a 45-minute workout at the gym.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 30, 'is_active' => true],
            ['title' => '20-minute cycling', 'description' => 'Ride a bike for 20 minutes around your area.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 25, 'is_active' => true],

            // SPORT - INDIVIDUAL - HARD
            ['title' => '5km run', 'description' => 'Complete a 5km run and track your time.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'hard', 'xp_value' => 50, 'is_active' => true],
            ['title' => '1-hour swim', 'description' => 'Swim continuously for 1 hour at the pool.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'hard', 'xp_value' => 60, 'is_active' => true],
            ['title' => '100 push-ups challenge', 'description' => 'Complete 100 push-ups throughout the day.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'hard', 'xp_value' => 50, 'is_active' => true],

            // SPORT - GROUP - EASY
            ['title' => 'Walk with a classmate', 'description' => 'Go for a 20-minute walk with someone from your university.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'easy', 'xp_value' => 20, 'is_active' => true],
            ['title' => 'Stretch together', 'description' => 'Do a stretching session with a friend from your city.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'easy', 'xp_value' => 20, 'is_active' => true],

            // SPORT - GROUP - MEDIUM
            ['title' => 'Play basketball together', 'description' => 'Find someone who loves basketball and play a game together.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'medium', 'xp_value' => 40, 'is_active' => true],
            ['title' => 'Go swimming together', 'description' => 'Head to the pool with a match who also loves swimming.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'medium', 'xp_value' => 40, 'is_active' => true],
            ['title' => 'Tennis match', 'description' => 'Play a friendly tennis match with someone nearby.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'medium', 'xp_value' => 45, 'is_active' => true],

            // SPORT - GROUP - HARD
            ['title' => 'Run 5km together', 'description' => 'Complete a 5km run together with a fellow student.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'hard', 'xp_value' => 60, 'is_active' => true],

            // SPORT - CREATED
            ['title' => 'Organize a football match', 'description' => 'Create a football event and invite students from your university.', 'category' => 'sport', 'subcategory' => 'created', 'difficulty' => 'hard', 'xp_value' => 80, 'is_active' => true],
            ['title' => 'Host a yoga session', 'description' => 'Organize an outdoor yoga session and invite others to join.', 'category' => 'sport', 'subcategory' => 'created', 'difficulty' => 'medium', 'xp_value' => 70, 'is_active' => true],
            ['title' => 'Group hiking event', 'description' => 'Plan a hiking trip and invite students from your city.', 'category' => 'sport', 'subcategory' => 'created', 'difficulty' => 'hard', 'xp_value' => 90, 'is_active' => true],

            // MEAL - INDIVIDUAL - EASY
            ['title' => 'Eat a healthy breakfast', 'description' => 'Prepare and eat a nutritious breakfast at home.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 10, 'is_active' => true],
            ['title' => 'Drink 2 liters of water', 'description' => 'Stay hydrated by drinking at least 2 liters of water today.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 10, 'is_active' => true],
            ['title' => 'Eat a fruit or vegetable', 'description' => 'Include at least one fruit or vegetable in your meals today.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 10, 'is_active' => true],

            // MEAL - INDIVIDUAL - MEDIUM
            ['title' => 'Cook a meal from scratch', 'description' => 'Prepare a full meal at home without processed food.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 25, 'is_active' => true],
            ['title' => 'Meal prep for 3 days', 'description' => 'Prepare meals for the next 3 days in advance.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 35, 'is_active' => true],
            ['title' => 'Try a new healthy recipe', 'description' => 'Find and cook a new healthy recipe you have never tried.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 30, 'is_active' => true],

            // MEAL - INDIVIDUAL - HARD
            ['title' => 'Cook a 3-course meal', 'description' => 'Prepare a full 3-course meal entirely from scratch.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'hard', 'xp_value' => 50, 'is_active' => true],
            ['title' => 'No processed food for a day', 'description' => 'Avoid all processed food for an entire day.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'hard', 'xp_value' => 55, 'is_active' => true],

            // MEAL - GROUP - EASY
            ['title' => 'Have lunch together', 'description' => 'Eat lunch with a fellow student from your university.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'easy', 'xp_value' => 20, 'is_active' => true],
            ['title' => 'Grab a coffee together', 'description' => 'Meet up with a match for a coffee break.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'easy', 'xp_value' => 15, 'is_active' => true],

            // MEAL - GROUP - MEDIUM
            ['title' => 'Cook together', 'description' => 'Cook a meal together with someone who shares your food preferences.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'medium', 'xp_value' => 40, 'is_active' => true],
            ['title' => 'Try street food together', 'description' => 'Explore local street food spots with a match.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'medium', 'xp_value' => 35, 'is_active' => true],

            // MEAL - CREATED
            ['title' => 'Organize a potluck dinner', 'description' => 'Host a potluck where everyone brings a dish to share.', 'category' => 'meal', 'subcategory' => 'created', 'difficulty' => 'hard', 'xp_value' => 80, 'is_active' => true],
            ['title' => 'Plan a group cooking session', 'description' => 'Organize a cooking event where students prepare a meal together.', 'category' => 'meal', 'subcategory' => 'created', 'difficulty' => 'hard', 'xp_value' => 75, 'is_active' => true],

            // MENTAL HEALTH - INDIVIDUAL - EASY
            ['title' => '5-minute meditation', 'description' => 'Take 5 minutes to sit quietly and focus on your breathing.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 10, 'is_active' => true],
            ['title' => 'Write in a journal', 'description' => 'Spend 10 minutes writing about your thoughts and feelings.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 10, 'is_active' => true],
            ['title' => 'Take a digital detox hour', 'description' => 'Spend one hour without any screens or social media.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'easy', 'xp_value' => 15, 'is_active' => true],

            // MENTAL HEALTH - INDIVIDUAL - MEDIUM
            ['title' => '20-minute meditation', 'description' => 'Complete a guided 20-minute meditation session.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 25, 'is_active' => true],
            ['title' => 'Read for 30 minutes', 'description' => 'Read a book of your choice for at least 30 minutes.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 20, 'is_active' => true],
            ['title' => 'Practice gratitude', 'description' => 'Write down 5 things you are grateful for today.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'medium', 'xp_value' => 20, 'is_active' => true],

            // MENTAL HEALTH - INDIVIDUAL - HARD
            ['title' => 'Complete a full mindfulness day', 'description' => 'Practice mindfulness throughout the entire day, noting your thoughts.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'hard', 'xp_value' => 60, 'is_active' => true],
            ['title' => 'Digital detox for a full day', 'description' => 'Avoid all social media and non-essential screen time for 24 hours.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'hard', 'xp_value' => 70, 'is_active' => true],

            // MENTAL HEALTH - GROUP - EASY
            ['title' => 'Have a meaningful conversation', 'description' => 'Have a deep, meaningful conversation with someone from your university.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'easy', 'xp_value' => 20, 'is_active' => true],
            ['title' => 'Watch a movie together', 'description' => 'Watch a feel-good movie with a fellow student.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'easy', 'xp_value' => 25, 'is_active' => true],

            // MENTAL HEALTH - GROUP - MEDIUM
            ['title' => 'Study together', 'description' => 'Study with a fellow student to reduce academic stress.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'medium', 'xp_value' => 30, 'is_active' => true],

            // MENTAL HEALTH - CREATED
            ['title' => 'Organize a study group', 'description' => 'Create a study group session to reduce academic stress together.', 'category' => 'mental_health', 'subcategory' => 'created', 'difficulty' => 'hard', 'xp_value' => 60, 'is_active' => true],
            ['title' => 'Host a board game night', 'description' => 'Organize a relaxing board game evening with fellow students.', 'category' => 'mental_health', 'subcategory' => 'created', 'difficulty' => 'medium', 'xp_value' => 65, 'is_active' => true],
        ];

        foreach ($tasks as $task) {
            DB::table('tasks')->insert([
                ...$task,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
