<?php


//namespace App\Http\Controllers;
//
//use Carbon\Carbon;
//use Illuminate\Http\Request;
//use App\Models\PiggyBank;
//use Illuminate\Support\Facades\Log;
//
//class TestController extends Controller
//{
//    public function testPiggyBank(Request $request, $piggyBankId)
//    {
//        try {
//            // 1. Set the test date
//            $testDate = '2026-03-15';
//            Carbon::setTestNow(Carbon::parse($testDate));
//            // Log::info("Test date set to: ".$testDate);
//
//            // 2. Get the piggy bank
//            $piggyBank = PiggyBank::findOrFail($piggyBankId);
//            // Log::info("Found piggy bank with status: ".$piggyBank->status);
//
//            // 3. Get schedule before resume
//            $beforeSchedule = $piggyBank->scheduledSavings()
//                ->select(['saving_number', 'saving_date', 'status'])
//                ->orderBy('saving_number')
//                ->limit(1) // Just get first record to test
//                ->get();
//
//            Log::info("Got before schedule");
//
//            return response()->json([
//                'test_date' => Carbon::now()->format('Y-m-d'),
//                'piggy_bank_status' => $piggyBank->status,
//                'first_scheduled_saving' => $beforeSchedule->first() ? [
//                    'saving_number' => $beforeSchedule->first()->saving_number,
//                    'date' => $beforeSchedule->first()->saving_date,
//                    'status' => $beforeSchedule->first()->status
//                ] : null
//            ]);
//
//        } catch (\Exception $e) {
//            Log::error("Test failed: ".$e->getMessage());
//            return response()->json([
//                'error' => $e->getMessage(),
//                'file' => $e->getFile(),
//                'line' => $e->getLine()
//            ], 500);
//        }
//    }
//}


namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\PiggyBank;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function testPiggyBank(Request $request, $piggyBankId)
    {
        try {
            // 1. Set the test date
            $testDate = '2026-03-15';
            Carbon::setTestNow(Carbon::parse($testDate));
            Log::info("Test date set to: ".$testDate);

            // 2. Get the piggy bank
            $piggyBank = PiggyBank::findOrFail($piggyBankId);
            Log::info("Found piggy bank with status: ".$piggyBank->status);

            // 3. Get schedule before resume
            $beforeSchedule = $piggyBank->scheduledSavings()
                ->select(['saving_number', 'saving_date', 'status'])
                ->orderBy('saving_number')
                ->get();

            Log::info("Got before schedule with ".$beforeSchedule->count()." savings");

            // 4. Try resume operation
            Log::info("Attempting to resume piggy bank");
            $piggyBank->update(['status' => 'active']);
            Log::info("Updated piggy bank status to active");

            // Get pending savings
            $pendingSavings = $piggyBank->scheduledSavings()
                ->where('status', 'pending')
                ->orderBy('saving_number', 'asc')
                ->get();

            Log::info("Found ".$pendingSavings->count()." pending savings");

            // Start recalculating from today's date
            $newStartDate = Carbon::today();
            Log::info("New start date set to: ".$newStartDate->format('Y-m-d'));

            foreach ($pendingSavings as $saving) {
                Log::info("Processing saving #".$saving->saving_number);
                $saving->update(['saving_date' => $newStartDate->copy()->addYears($saving->saving_number - 1)]);
                Log::info("Updated saving date to: ".$newStartDate->copy()->addYears($saving->saving_number - 1)->format('Y-m-d'));
            }

            // 5. Get schedule after resume
            $afterSchedule = $piggyBank->fresh()->scheduledSavings()
                ->select(['saving_number', 'saving_date', 'status'])
                ->orderBy('saving_number')
                ->get();

            Log::info("Got after schedule");

            return response()->json([
                'test_date' => Carbon::now()->format('Y-m-d'),
                'before_resume' => $beforeSchedule->map(function ($saving) {
                    return [
                        'saving_number' => $saving->saving_number,
                        'date' => $saving->saving_date,
                        'status' => $saving->status
                    ];
                }),
                'after_resume' => $afterSchedule->map(function ($saving) {
                    return [
                        'saving_number' => $saving->saving_number,
                        'date' => $saving->saving_date,
                        'status' => $saving->status
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error("Test failed: ".$e->getMessage());
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}
