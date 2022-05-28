<?php

namespace FlawidDSouza\QuickAdminPanelLaravel;

// To use this class all your records must have a correct predefined sort order

class TableSorter
{
    private $table;
    private $sortColumn;
    private $sortColumnValue;
    private $type;

    public static function table($table) {
        $obj = new static();
        $obj->table = \DB::table($table);
        return $obj;
    }

    public function where($column, $value) {
        $this->table = $this->table->where($column, $value);
        return $this;
    }

    public function before($column, $value) {
        $this->sortColumn = $column;
        $this->sortColumnValue = $value;
        $this->type = 'before';
        return $this;
    }

    public function after($column, $value) {
        $this->sortColumn = $column;
        $this->sortColumnValue = $value;
        $this->type = 'after';
        return $this;
    }

    private function handleBefore() {
        $sortOrder = $this->getTable()->where($this->sortColumn, $this->sortColumnValue)->first()->sort_order;
        $allRecords = $this->getTable()->orderBy('sort_order')->get();
        $startUpdate = false;
        foreach($allRecords as $record) {
            $prop = $this->sortColumn;
            if($record->$prop == $this->sortColumnValue) {
                $startUpdate = true;
            }
            if($startUpdate) { // increment all sort orders from the sort column
                $this->getTable()->where('id', $record->id)->update(['sort_order' => $record->sort_order + 1]);
            }
        }
        return $sortOrder;
    }

    private function handleAfter() {
        $sortOrder = $this->getTable()->where($this->sortColumn, $this->sortColumnValue)->first()->sort_order + 1;
        $allRecords = $this->getTable()->orderBy('sort_order')->get();
        $startUpdate = false;
        foreach($allRecords as $record) {
            if($startUpdate) { // increment all sort orders after the sort column
                $this->getTable()->where('id', $record->id)->update(['sort_order' => $record->sort_order + 1]);
            }
            $prop = $this->sortColumn;
            if($record->$prop == $this->sortColumnValue) {
                $startUpdate = true;
            }
        }
        return $sortOrder;
    }

    private function getTable() {
        return (clone $this->table);
    }

    public function get() {
        switch($this->type) {
            case 'before':
                return $this->handleBefore();
                break;
            case 'after':
                return $this->handleAfter();
                break;
        }
    }
}
