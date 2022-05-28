<?php

namespace FlawidDSouza\QuickAdminPanelLaravel\Traits;

use FlawidDSouza\QuickAdminPanelLaravel\Paginator;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

trait RESTActions
{
    public function index(Request $request)
    {
        $orderByColumn = 'updated_at';
        $orderByOrder = 'DESC';

        if(defined('self::ORDER_BY')) {
            $orderByColumn = self::ORDER_BY[0] ? self::ORDER_BY[0] : 'updated_at';
            $orderByOrder = self::ORDER_BY[1] ? self::ORDER_BY[1] : 'DESC';
        }

        return Paginator::generate(
            self::MODEL::select('*'),
            [
                'sortBy' => $orderByColumn,
                'sortOrder' => $orderByOrder,
                'filterColumns' => self::FIELDS
            ],
            $request
        );
    }

    public function store(Request $request)
    {
        $m = self::MODEL;

        if(defined('self::UNIQUE_FIELDS') && count(self::UNIQUE_FIELDS) > 0) {
            $duplicatesCount = self::MODEL;
            foreach(self::UNIQUE_FIELDS as $index => $uniqueField) {
                if($index === 0) {
                    $duplicatesCount = $duplicatesCount::whereRaw("LOWER($uniqueField) = LOWER(?)", [$request[$uniqueField]]);
                } else {
                    $duplicatesCount = $duplicatesCount->whereRaw("LOWER($uniqueField) = LOWER(?)", [$request[$uniqueField]]);
                }
            }
            $duplicatesCount = $duplicatesCount->count();
            if($duplicatesCount > 0) {
                return $this->respond(Response::HTTP_BAD_REQUEST, 'Duplicate Entry');
            }
        }

        $additionalData = [];

        if(method_exists(get_called_class(), 'restActionsBeforeCreateHook')) {
            [$additionalData, $response] = self::restActionsBeforeCreateHook($request);
            if($response) {
                return $response;
            }
        }

        $createdRecord = $m::create(array_merge($request->only(self::FIELDS), $additionalData));

        if(method_exists(get_called_class(), 'restActionsAfterCreateHook')) {
            self::restActionsAfterCreateHook($createdRecord, $request);
        }

        return $this->respond(Response::HTTP_CREATED, $createdRecord);
    }

    public function update(Request $request, $id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }

        if(defined('self::UNIQUE_FIELDS') && count(self::UNIQUE_FIELDS) > 0) {
            $duplicatesCount = self::MODEL;
            $duplicatesCount = $duplicatesCount::where('id', '!=', $id);

            foreach(self::UNIQUE_FIELDS as $uniqueField) {
                $duplicatesCount = $duplicatesCount->whereRaw("LOWER($uniqueField) = LOWER(?)", [$request[$uniqueField]]);
            }
            $duplicatesCount = $duplicatesCount->count();
            if($duplicatesCount>0) {
                return $this->respond(Response::HTTP_BAD_REQUEST, 'Duplicate Entry');
            }
        }

        $additionalData = [];

        if(method_exists(get_called_class(), 'restActionsBeforeUpdateHook')) {
            [$additionalData, $response] = self::restActionsBeforeUpdateHook($request);
            if($response) {
                return $response;
            }
        }

        $model->update(array_merge($request->only(self::FIELDS), $additionalData));

        if(method_exists(get_called_class(), 'restActionsAfterUpdateHook')) {
            self::restActionsAfterUpdateHook($model, $request);
        }

        return $this->respond(Response::HTTP_OK, $model);
    }

    public function destroy($id)
    {
        $m = self::MODEL;
        $model = $m::find($id);
        if(is_null($model)) {
            return $this->respond(Response::HTTP_NOT_FOUND);
        }
        try {
            $m::destroy($id);

            if(method_exists(get_called_class(), 'restActionsAfterDestroyHook')) {
                self::restActionsAfterDestroyHook();
            }
        } catch(\Throwable $e) {
            return $this->respond(Response::HTTP_BAD_REQUEST, 'Entry in use');
        }

        return $this->respond(Response::HTTP_NO_CONTENT);
    }

    protected function respond($status, $data = [])
    {
        return response()->json($data, $status);
    }
}
