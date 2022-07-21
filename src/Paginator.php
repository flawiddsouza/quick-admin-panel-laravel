<?php

namespace FlawidDSouza\QuickAdminPanelLaravel;

use FlawidDSouza\QuickAdminPanelLaravel\Excel;

class Paginator
{
    public static function generate($query, $params, $request)
    {
        $unfilteredTotal = $query->count();
        $paginator = $query;

        if(isset($params['filterColumns']) && isset($request->filter) && $request->filter !== '') {
            $paginator->where(function($where) use($params, $request) {
                foreach($params['filterColumns'] as $filterColumn) {
                    $where->orWhere($filterColumn, 'like', '%' . $request->filter . '%');
                }
            });
        }

        if($request->sort_by) {
            $sortByColumn = $params['requestSortBySubtitutions'][$request->sort_by] ?? $request->sort_by;
            $paginator = $paginator->orderBy($sortByColumn, $request->sort_order);
        } else {
            if(isset($params['sortBy'])) {
                $paginator = $paginator->orderBy($params['sortBy'], $params['sortOrder']);
            }
        }

        if($request->export) {
            return static::export($request, $paginator);
        }

        $paginator = $paginator->paginate(50);

        return [
            'paginator' => $paginator,
            'unfiltered_total' => $unfilteredTotal
        ];
    }

    private static function export($request, $m)
    {
        ini_set('memory_limit', '-1');

        $fields = json_decode($request->fields, true);

        $rows = $m->get();

        $exportArray = [];

        foreach($rows as $index => $row) {
            $exportArray[$index] = [];
            foreach($fields as $field) {
                $exportArray[$index][] = $row[$field['field']];
            }
        }

        return Excel::download(Excel::createSpreadsheetFromArray([array_column($fields, 'label'), ...$exportArray]), 'export.xlsx');
    }
}
