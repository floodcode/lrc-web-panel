<?php

class Paginator
{
    private $module;
    private $table;
    private $pages;
    private $page;
    private $limit;
    private $result;
    
    function __construct($module, $table, $where, $page, $limit)
    {
        $this->module = $module;
        $this->table = $table;
        $this->limit = $limit;
        $this->page = $page;
        
        $this->result = array();
        
        $stmt = $GLOBALS['DB']->prepare('SELECT COUNT(*) FROM ' . $table);
        $stmt->execute();
        
        $items = $stmt->fetchColumn();
        $this->pages = ceil($items / $limit);
    }
    
    private function generate_link($page, $text)
    {        
        if ($this->page == $page || $page == 'none')
        {
            array_push($this->result, array('text' => $text, 'url' => 'none'));
        }
        else
        {
            $pg_ulr = '/' . $this->module . '/index/?page=' . $page . '&perpage=' . $this->limit;
            array_push($this->result, array('text' => $text, 'url' => $pg_ulr));
        }
    }
    
    private function generate_by_range($from, $to)
    {
        for ($i = $from; $i <= $to; $i++)
        {
            $this->generate_link($i, $i);
        }
    }
    
    public function get_paginator()
    {
        $page = $this->page;
        $pages = $this->pages;
        
        // Previous page
        $this->generate_link($page != 1 ? $page - 1 : 'none', '&lsaquo;');
        
        // Inner pages
        if ($pages <= 10)
        {
            $this->generate_by_range(1, $pages);
        }
        else
        {
            if ($page < 6)
            {
                $this->generate_by_range(1, $page + 2);
            }
            else
            {
                $this->generate_by_range(1, 2);
                $this->generate_link('none', '..');
            }
            
            if ($page >= 6 && $page <= $pages - 6)
            {
                $this->generate_by_range($page - 2, $page + 2);
            }
            
            if ($page > $pages - 6)
            {
                $this->generate_by_range($page - 2, $pages);
            }
            else
            {
                $this->generate_link('none', '..');
                $this->generate_by_range($pages - 1, $pages);
            }
        }
        
        // Next page
        $this->generate_link($page != $pages ? $page + 1 : 'none', '&rsaquo;');
        
        return $this->result;
    }
}