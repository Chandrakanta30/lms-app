<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    private const FREQUENCY_MAP = [
        'monthly'     => ['count' => 12, 'gap' => 1],
        'quarterly'   => ['count' => 3,  'gap' => 4],
        'half_yearly' => ['count' => 2,  'gap' => 6],
        'yearly'      => ['count' => 1,  'gap' => 12],
    ];

    public function up(): void
    {
        $plans = DB::table('training_modules')
            ->where('is_anuual', '1')
            ->whereNull('annual_parent_id')
            ->get();

        foreach ($plans as $plan) {
            $config = self::FREQUENCY_MAP[$plan->frequency] ?? null;

            if (! $config) {
                continue;
            }

            $planStart = Carbon::parse($plan->start_date ?: $plan->created_at);

            
            $programs = DB::table('training_modules')
                ->where('annual_parent_id', $plan->id)
                ->orderBy('id')
                ->get();

            foreach ($programs->values() as $index => $program) {
                if ($index >= $config['count']) {
                    break;
                }

                $period = $this->periodFor($planStart, $index, $config['gap']);

                DB::table('training_modules')
                    ->where('id', $program->id)
                    ->update([
                        'name'       => $plan->name . ' - ' . $period['label'] . ' Training',
                        'start_date' => $period['start']->toDateString(),
                        'end_date'   => $period['end']->toDateString(),
                    ]);
            }
        }
    }

    public function down(): void
    {
        
    }

 
    private function periodFor(Carbon $planStart, int $index, int $gap): array
    {
        $year = $planStart->year;
        $startMonth = 1 + ($index * $gap);
        $endMonth = min(12, $startMonth + $gap - 1);

        // The plan's day-of-month is reused on BOTH ends, clamped per month so a
        // plan starting on the 31st still lands inside shorter months.
        $startDay = min($planStart->day, Carbon::create($year, $startMonth, 1)->daysInMonth);
        $endDay = min($planStart->day, Carbon::create($year, $endMonth, 1)->daysInMonth);

        $start = Carbon::create($year, $startMonth, $startDay)->startOfDay();
        $end = Carbon::create($year, $endMonth, $endDay)->startOfDay();

        $label = $startMonth === $endMonth
            ? $start->format('F')
            : $start->format('F') . ' to ' . $end->format('F');

        return compact('start', 'end', 'label');
    }
};
