<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Http\Controllers\UserController as UserController;
use App\Models\Partner;
use Validator;

use MongoDB\BSON\ObjectID;


class PartnerController extends UserController
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $partners = Partner::all();

        return $this->sendResponse($partners->toArray(), 'Partners retrieved successfully.');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();


        $validator = Validator::make($input, [
            'name' => 'required',
            'address' => 'required',
            'tel' => 'required',
            'tax' => 'required',
        ]);


        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());       
        }

        $input['code'] = $this->generatePartnerCode();
        $input['active'] = (bool)$input['active'];
        $input['created_by'] = new ObjectID($this->userData->_id);
        $input['updated_by'] = new ObjectID($this->userData->_id);

        $partner = Partner::create($input);

        return $this->sendResponse($partner->toArray(), 'Partner created successfully.');
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $partner = Partner::find($id);


        if (is_null($partner)) {
            return $this->sendError('Partner not found.');
        }


        return $this->sendResponse($partner->toArray(), 'Partner retrieved successfully.');
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Partner $partner)
    {
        $input = $request->all();


        // $validator = Validator::make($input, [
        //     'name' => 'required',
        //     'address' => 'required',
        //     'tel' => 'required',
        //     'tax' => 'required',
        // ]);

        // if($validator->fails()){
        //     return $this->sendError('Validation Error.', $validator->errors());       
        // }

        $partner->name = isset($input['name']) ? $input['name'] : $partner->name;
        $partner->address = isset($input['address']) ? $input['address'] : $partner->address;
        $partner->tel = isset($input['tel']) ? $input['tel'] : $partner->tel;
        $partner->tax = isset($input['tax']) ? $input['tax'] : $partner->tax;
        $partner->active = isset($input['active']) ? (bool)$input['active'] : $partner->active;
        $partner->mobile = isset($input['mobile']) ? $input['mobile'] : $partner->mobile;
        $partner->fax = isset($input['fax']) ? $input['fax'] : $partner->fax;
        $partner->updated_by = new ObjectID($this->userData->_id);
        $partner->save();

        return $this->sendResponse($partner->toArray(), 'Partner updated successfully.');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Partner $partner)
    {
        $partner->deleted_by = new ObjectID($this->userData->_id);
        $partner->save();
        $partner->delete();

        return $this->sendResponse($partner->toArray(), 'Partner deleted successfully.');
    }

    private function generatePartnerCode()
    {
        $number = 1;
        $lastPartner = Partner::withTrashed()->orderBy('_id', 'DESC')->first();
        if($lastPartner) {
            $number = (int)substr($lastPartner->code, -6) + 1;
        }
        return 'PN' . sprintf("%'06d", $number);
    }
}