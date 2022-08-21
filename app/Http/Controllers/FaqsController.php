<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFaq;
use App\Http\Requests\EditFaq;
use App\Models\Faqs;
use Illuminate\Http\Request;

class FaqsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = Faqs::latest()->paginate(request()->input("page_number"));
        return response()->json([
            "status" => "success",
            "message" => "Faq Fetched Successfully",
            "faq" => $plans,
        ], 200);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(CreateFaq $request)
    {
        $data = $request->validated();
        return $this->store($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($data)
    {
        $faq = Faqs::create($data);
        return response()->json(["message" => "Faq Created", "status" => "success", "faq" => $faq], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Faqs  $faqs
     * @return \Illuminate\Http\Response
     */
    public function show(Faqs $faqs)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Faqs  $faqs
     * @return \Illuminate\Http\Response
     */
    public function edit(EditFaq $request)
    {
        $data = $request->validated();
        // return $data;
        $faq = Faqs::where("id", $data["id"])->first();
        if ($faq == null) {
            return response()->json(["message" => "not found", "status" => "error"], 400);
        }
        $faq->update($data);
        $faq->save();
        return response()->json(["message" => "Faq Success", "status" => "success", "faq" => $faq], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Faqs  $faqs
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Faqs $faqs)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Faqs  $faqs
     * @return \Illuminate\Http\Response
     */
    public function destroy(Faqs $faqs)
    {
        //
    }
}
