<?php
namespace Dazzle\PgSQL\Result;

interface TupleResultStatement
{
    public function fetchColumn();
    public function fetchRow();
    public function fetchAll();
    public function fetchAssoc();
    public function fetchObject();
}