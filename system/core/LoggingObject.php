<?php


abstract class LoggingObject extends TableObject {

    public final function save() {
        //Do nothing - LoggingObjects can't be updated, only created.
    }

    public final function delete() {
        //Do nothing - LoggingObjects can't be deleted.
    }

    protected static function get_editable_column_list() {
        return array(); //LoggingObjects don't have any editable members.
    }
} 