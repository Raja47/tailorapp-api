<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Dress;
use App\Models\Measurement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DressController extends Controller
{
    // Create Table dresses( 
    //     id Integer not Null Primary Key AutoIncrement,  
    //     orderId Integer Not Null,
    //     tailor_id Integer Not Null,
    //     shop_id Integer Not Null,
    //     categoryId  Integer Not Null,
    //     name Text not Null,
    //     gender Text Default 'male',
    //     type Text not Null Default 'new',
    //     quantity Integer Not Null,
    //     price Int Not Null,
    //     deliveryDate  Timestamp DATETIME DEFAULT Null,
    //     trialDate Timestamp DATETIME DEFAULT Null,
    //     isUrgent Int Default 0,
    //     notes Text DEFAULT Null,
    //     status Integer Default 1,
    //     deletedAt Timestamp DATETIME DEFAULT Null,
    //     createdAt Timestamp DATETIME DEFAULT CURRENT_TIMESTAMP)


    public function getOrderDressMeasurement($tailor_id,$dress_id)
    {
        $measurement = Measurement::where([['model', 'dress'], ['model_id', $dress_id]])->first();
        return response()->json(['success' => true, 'message' => 'Dress Measurement', 'data' => ['Dress id' => $dress_id, 'Measurement' => $measurement]], 200);
    }

    public function getTabDresses($tailor_id, Request $request)
    {
        $rules = [
            'shop_id' => 'required',
            'tabName' => 'required',
            'search' => '',
        ];
        $now = Carbon::now();
        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $shop_id = $request->shop_id;
            $tabName = $request->tabName;
            $search = $request->search;
            $query = DB::table('dresses')
                ->select('dresses.*', 'categories.name AS catName', 'pictures.path AS picture', 'customers.name AS customername')
                ->leftjoin('categories', 'categories.id', '=', 'dresses.category_id')
                ->leftjoin('orders', 'orders.id', '=', 'dresses.order_id')
                ->leftjoin('customers', 'customers.id', '=', 'orders.customer_id')
                ->leftjoin('pictures', function ($join) {
                    $join->on('pictures.model_id', '=', 'dresses.id');
                    $join->where('pictures.model', '=', 'dress');
                });

            switch ($tabName) {
                case 'new':
                    if (empty($search)) {
                        $dresses = $query->where('dresses.status', '=', 0)->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->where('dresses.status', '=', 0)
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'urgent':
                    if (empty($search)) {
                        $dresses = $query->where('dresses.is_urgent', '=', 0)->whereNotIn('dresses.status', [2, 3, 4, 5])->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->where('dresses.is_urgent', '=', 0)
                            ->whereNotIn('dresses.status', [2, 3, 4, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'dueDresses':
                    $due_time_list = [$now->format('y-m-d'), $now->copy()->addDays(1)->format('y-m-d'), $now->copy()->addDays(2)->format('y-m-d')];
                    if (empty($search)) {
                        $dresses = $query->whereIn('dresses.delivery_date', $due_time_list)->whereNotIn('dresses.status', [2, 3, 4, 5])->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->whereIn('dresses.delivery_date', $due_time_list)
                            ->whereNotIn('dresses.status', [2, 3, 4, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'lateDresses':
                    $currentDate = Carbon::now();
                    if (empty($search)) {
                        $dresses = $query->whereNotIn('dresses.status', [1, 2, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where('dresses.delivery_date', '<', $currentDate)
                            ->get();
                    } else {
                        $dresses = $query->whereNotIn('dresses.status', [1, 2, 5])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where('delivery_date', '<', $currentDate)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'inProgress':
                    if (empty($search)) {
                        $dresses = $query->where('dresses.status', 1)->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->where('dresses.status', 1)
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;

                case 'completed/delivered':
                    if (empty($search)) {
                        $dresses = $query->whereIn('dresses.status', [2, 3])->where('dresses.shop_id', '=', $shop_id)->get();
                    } else {
                        $dresses = $query->whereIn('dresses.status', [2, 3])
                            ->where('dresses.shop_id', '=', $shop_id)
                            ->where(function ($query) use ($search) {
                                $query->where('dresses.name', 'like', '%' . $search . '%')
                                    ->orWhere('categories.name', 'like', '%' . $search . '%');
                            })
                            ->get();
                    }
                    // return $dresses;
                    break;
            }

            return $dresses;
        }
    }

    public function addDress($tailor_id, Request $request)
    {
        $rules = [
            'order_id' => 'required',
            'shop_id' => 'required',
            'category_id' => 'required',
            'type' => 'required',
            'quantity' => 'required',
            'price' => 'required',
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $dress = Dress::create([
                'order_id' => $request->order_id,
                'tailor_id' => $tailor_id,
                'shop_id' => $request->shop_id,
                'category_id' => $request->category_id,
                'name' => '',
                'type' => $request->type,
                'quantity' => $request->quantity,
                'price' => $request->price,
                'delivery_date' => $request->delivery_date,
                'trial_date' => $request->trial_date,
                'is_urgent' => $request->is_urgent,
                'status' => 0,
                'notes' => $request->notes,

            ]);

            if ($dress->save()) {
                $dress_name = Dress::where('id', $dress->id)->first();
                $dress_name->name = '#D-' . $dress->category_id . '-' . $dress->id;
                return response()->json(['success' => true, 'message' => 'Dress Created Successfully', 'data' => ['Dress id' => $dress->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress could bot be created'], 500);
            }
        }
    }


    public function updateDress($tailor_id, Request $request)
    {
        $rules = [
            'dress_id' => 'required'
        ];
        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $dress = Dress::where([['id', $request->dress_id], ['tailor_id', $tailor_id]])->first();
            $dress->category_id = $request->category_id;
            $dress->name = '';
            $dress->type = $request->type;
            $dress->quantity = $request->quantity;
            $dress->price = $request->price;
            $dress->delivery_date = $request->delivery_date;
            $dress->trial_date = $request->trial_date;
            $dress->is_urgent = $request->is_urgent;
            $dress->notes = $request->notes;

            if ($dress->save()) {
                return response()->json(['success' => true, 'message' => 'Dress Updated Successfully', 'data' => ['Dress id' => $dress->id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress cannot be updated'], 500);
            }
        }
    }

    public function countDressesByStatus($tailor_id, $shop_id, $index)
    {
        $now = Carbon::now();
        switch ($index) {
            case 0:
                //this month
                $time = $now->copy()->subDay(10);
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->where('created_at', '<', $time)
                    ->groupBy('status')
                    ->get();
                break;

            case 1:
                //last 30 days
                $time = $now->copy()->subDay(30);
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->where('created_at', '<', $time)
                    ->groupBy('status')
                    ->get();
                break;

            case 2:
                //all
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->groupBy('status')
                    ->get();
                break;
                
            default:
                $dresses = Dress::select('status')
                    ->selectRaw('SUM(quantity) as countDress')
                    ->selectRaw('SUM(quantity * price) as amount')
                    ->where('tailor_id', $tailor_id)
                    ->where('shop_id', $shop_id)
                    ->groupBy('status')
                    ->get();
                break;
        }

        return $dresses;
    }

    public function countDresses()
    {
        $dress = Dress::where('status', '!=', 4)
                ->selectRaw('SUM(quantity) as countDress')
                ->selectRaw('SUM(quantity * price) as totalAmount')
                ->get();
        return $dress;
    }

    public function delete($tailor_id, $dress_id)
    {
        $dress = Dress::where([['id', $dress_id], ['tailor_id', $tailor_id]])->get();
        $dress->delete();
        return response()->json(['success' => true, 'message' => 'Dress Deleted', 'data' => ['countDeletes' => $dress->count()]], 200);
    }

    public function getOrderDresses($tailor_id, $order_id)
    {
        $query = DB::table('dresses')
            ->select('dresses.*', 'categories.name AS catName', 'pictures.path AS picture')
            ->leftjoin('categories', 'categories.id', '=', 'dresses.category_id')
            ->leftjoin('pictures', function ($join) {
                $join->on('pictures.model_id', '=', 'dresses.id');
                $join->where('pictures.model', '=', 'dress');
            });
        $dresses = $query->where('tailor_id', $tailor_id)->where('order_id', $order_id)->get();
        return $dresses;
    }

    public function updateStatus($tailor_id, Request $request)
    {
        $rules = [
            'dress_id' => 'required',
            'status_id' => 'required'
        ];

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails()) {
            return response()->json(['success' => false, 'message' => 'Dress data validation error', 'data' => $validation->errors()], 422);
        } else {
            $dress = Dress::where([['id', $request->dress_id], ['tailor_id', $tailor_id]])->first();
            $dress->status = $request->status_id;

            if ($dress->save()) {
                return response()->json(['success' => true, 'message' => 'Dress Status Updated Successfully', 'data' => ['Dress id' => $request->dress_id]], 200);
            } else {
                return response()->json(['success' => false, 'message' => 'Dress Status could bot be updated'], 500);
            }
        }
    }
}
