<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $subcategoryIds = DB::table('subcategories')->pluck('id', 'name');

        $tasks = [
            // SPORT - INDIVIDUAL - EASY
            ['title' => '20-minute walk', 'description' => 'Take a relaxing 20-minute walk outside.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'easy'],
            ['title' => '10-minute stretching', 'description' => 'Do a full body stretching routine for 10 minutes.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'easy'],
            ['title' => '15-minute yoga', 'description' => 'Follow a beginner yoga routine for 15 minutes.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'easy'],

            // SPORT - INDIVIDUAL - MEDIUM
            ['title' => '30-minute run', 'description' => 'Go for a 30-minute jog at a comfortable pace.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'medium'],
            ['title' => '45-minute gym session', 'description' => 'Complete a 45-minute workout at the gym.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'medium'],
            ['title' => '20-minute cycling', 'description' => 'Ride a bike for 20 minutes around your area.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'medium'],

            // SPORT - INDIVIDUAL - HARD
            ['title' => '5km run', 'description' => 'Complete a 5km run and track your time.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'hard'],
            ['title' => '1-hour swim', 'description' => 'Swim continuously for 1 hour at the pool.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'hard'],
            ['title' => '100 push-ups challenge', 'description' => 'Complete 100 push-ups throughout the day.', 'category' => 'sport', 'subcategory' => 'individual', 'difficulty' => 'hard'],

            // SPORT - GROUP - EASY
            ['title' => 'Walk with a classmate', 'description' => 'Go for a 20-minute walk with someone from your university.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'easy'],
            ['title' => 'Stretch together', 'description' => 'Do a stretching session with a friend from your city.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'easy'],

            // SPORT - GROUP - MEDIUM
            ['title' => 'Play basketball together', 'description' => 'Find someone who loves basketball and play a game together.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'medium'],
            ['title' => 'Go swimming together', 'description' => 'Head to the pool with a match who also loves swimming.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'medium'],
            ['title' => 'Tennis match', 'description' => 'Play a friendly tennis match with someone nearby.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'medium'],

            // SPORT - GROUP - HARD
            ['title' => 'Run 5km together', 'description' => 'Complete a 5km run together with a fellow student.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'hard'],

            // SPORT - GROUP - HARD (formerly 'created')
            ['title' => 'Organize a football match', 'description' => 'Create a football event and invite students from your university.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'hard'],
            ['title' => 'Host a yoga session', 'description' => 'Organize an outdoor yoga session and invite others to join.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'medium'],
            ['title' => 'Group hiking event', 'description' => 'Plan a hiking trip and invite students from your city.', 'category' => 'sport', 'subcategory' => 'group', 'difficulty' => 'hard'],

            // MEAL - INDIVIDUAL - EASY
            ['title' => 'Eat a healthy breakfast', 'description' => 'Prepare and eat a nutritious breakfast at home.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'easy'],
            ['title' => 'Drink 2 liters of water', 'description' => 'Stay hydrated by drinking at least 2 liters of water today.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'easy'],
            ['title' => 'Eat a fruit or vegetable', 'description' => 'Include at least one fruit or vegetable in your meals today.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'easy'],

            // MEAL - INDIVIDUAL - MEDIUM
            ['title' => 'Cook a meal from scratch', 'description' => 'Prepare a full meal at home without processed food.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'medium'],
            ['title' => 'Meal prep for 3 days', 'description' => 'Prepare meals for the next 3 days in advance.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'medium'],
            ['title' => 'Try a new healthy recipe', 'description' => 'Find and cook a new healthy recipe you have never tried.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'medium'],

            // MEAL - INDIVIDUAL - HARD
            ['title' => 'Cook a 3-course meal', 'description' => 'Prepare a full 3-course meal entirely from scratch.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'hard'],
            ['title' => 'No processed food for a day', 'description' => 'Avoid all processed food for an entire day.', 'category' => 'meal', 'subcategory' => 'individual', 'difficulty' => 'hard'],

            // MEAL - GROUP - EASY
            ['title' => 'Have lunch together', 'description' => 'Eat lunch with a fellow student from your university.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'easy'],
            ['title' => 'Grab a coffee together', 'description' => 'Meet up with a match for a coffee break.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'easy'],

            // MEAL - GROUP - MEDIUM
            ['title' => 'Cook together', 'description' => 'Cook a meal together with someone who shares your food preferences.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'medium'],
            ['title' => 'Try street food together', 'description' => 'Explore local street food spots with a match.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'medium'],

            // MEAL - GROUP - HARD (formerly 'created')
            ['title' => 'Organize a potluck dinner', 'description' => 'Host a potluck where everyone brings a dish to share.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'hard'],
            ['title' => 'Plan a group cooking session', 'description' => 'Organize a cooking event where students prepare a meal together.', 'category' => 'meal', 'subcategory' => 'group', 'difficulty' => 'hard'],

            // MENTAL HEALTH - INDIVIDUAL - EASY
            ['title' => '5-minute meditation', 'description' => 'Take 5 minutes to sit quietly and focus on your breathing.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'easy'],
            ['title' => 'Write in a journal', 'description' => 'Spend 10 minutes writing about your thoughts and feelings.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'easy'],
            ['title' => 'Take a digital detox hour', 'description' => 'Spend one hour without any screens or social media.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'easy'],

            // MENTAL HEALTH - INDIVIDUAL - MEDIUM
            ['title' => '20-minute meditation', 'description' => 'Complete a guided 20-minute meditation session.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'medium'],
            ['title' => 'Read for 30 minutes', 'description' => 'Read a book of your choice for at least 30 minutes.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'medium'],
            ['title' => 'Practice gratitude', 'description' => 'Write down 5 things you are grateful for today.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'medium'],

            // MENTAL HEALTH - INDIVIDUAL - HARD
            ['title' => 'Complete a full mindfulness day', 'description' => 'Practice mindfulness throughout the entire day, noting your thoughts.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'hard'],
            ['title' => 'Digital detox for a full day', 'description' => 'Avoid all social media and non-essential screen time for 24 hours.', 'category' => 'mental_health', 'subcategory' => 'individual', 'difficulty' => 'hard'],

            // MENTAL HEALTH - GROUP - EASY
            ['title' => 'Have a meaningful conversation', 'description' => 'Have a deep, meaningful conversation with someone from your university.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'easy'],
            ['title' => 'Watch a movie together', 'description' => 'Watch a feel-good movie with a fellow student.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'easy'],

            // MENTAL HEALTH - GROUP - MEDIUM
            ['title' => 'Study together', 'description' => 'Study with a fellow student to reduce academic stress.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'medium'],

            // MENTAL HEALTH - GROUP - HARD (formerly 'created')
            ['title' => 'Organize a study group', 'description' => 'Create a study group session to reduce academic stress together.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'hard'],
            ['title' => 'Host a board game night', 'description' => 'Organize a relaxing board game evening with fellow students.', 'category' => 'mental_health', 'subcategory' => 'group', 'difficulty' => 'medium'],
        ];

        foreach ($tasks as $task) {
            DB::table('tasks')->insert([
                'title'          => $task['title'],
                'description'    => $task['description'],
                'category'       => $task['category'],
                'subcategory_id' => $subcategoryIds[$task['subcategory']],
                'difficulty'     => $task['difficulty'],
                'is_active'      => true,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
}
