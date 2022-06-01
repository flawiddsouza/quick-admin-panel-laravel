<?php

namespace FlawidDSouza\QuickAdminPanelLaravel;

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

        $paginator = $paginator->paginate(50);

        return [
            'paginator' => $paginator,
            'unfiltered_total' => $unfilteredTotal
        ];
    }
}
