<?php
namespace OnPay\API\Util;

class Pagination{
    public $total;
    public $count;
    public $per_page;
    public $current_page;
    public $total_pages;
    public $links;

    function __construct(array $data){
        $this->total = $data["total"];
        $this->count = $data["count"];
        $this->per_page = $data["per_page"];
        $this->current_page = $data["current_page"];
        $this->total_pages = $data["total_pages"];
        $this->links = new \stdClass;
        if(count($data["links"]) > 0){
            foreach($data["links"] as $handle=>$link){
                $this->links->$handle = new Link($link);
            }
        }
    }
}