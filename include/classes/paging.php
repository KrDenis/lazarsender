<?php
    ###########################################
    # Thanks http://www.tigir.com/paging.htm
    #  
    # modified 22.10.2014 19:33 by me
    # changed DB extension to PDO
    #
    #  update 03.11.2014 23:11 
    #  delete all view from class
    #  Short manual:
    #    get_result_text() return string with info example: Show from 1 to 15 
    #    get_prev_page_link() if prev page has return array(link,prev_page_text) else empty string 
    #    get_next_page_link() if next page has return array(link,next_page_text) else empty string 
    #    get_page_links()  return array of arrays = array(url,pagenumber) for current page into array add 'pagenumber'
    #    
    ###########################################

class Paging { 


private $link_padding = 5; 
private $next_page_text = '&#187'; 
private $prev_page_text = '&#171'; 
private $result_text_pattern = 'Показано с %s по %s из %s'; 
private $page_var = 'p'; 

private $db; 
private $q; 
private $total_rows; 
private $total_pages; 
private $cur_page; 
private $page_size; 

public function __construct($db,$size = 10, $q='', $page_var='p') 
{ 
    $this->page_size = $size;
    $this->db = $db; 
    if ($q) $this->set_query($q); 
    $this->page_var = $page_var; 
    $this->cur_page = isset($_GET[$this->page_var]) && (int)$_GET[$this->page_var] > 0 ? (int)$_GET[$this->page_var] : 1; 
} 

public function set_query($q) 
{ 
    $this->q = $q; 
} 

public function set_page_size($page_size) 
{ 
    $this->page_size = abs((int)$page_size); 
} 

public function set_link_padding($padding) 
{ 
    $this->link_padding = abs((int)$padding); 
} 

public function get_page($q='') 
{ 
    if ($q) $this->set_query($q); 

    $stm = $this->db->prepare($this->query_paging($this->q));

    try {
        $stm->execute();
    } catch(PDOEcxeption $e){
        logging(implode(",",$stm->errorInfo()),false,__FILE__,__LINE__);
    }
    $r = $stm->fetchAll();

    $sth = $this->db->prepare('SELECT FOUND_ROWS()');

    try {
        $sth->execute();
    } catch(PDOEcxeption $e){
        logging(implode(",",$sth->errorInfo()),false,__FILE__,__LINE__);
    }

    $var_one = $sth->fetch();


    $this->total_rows = array_pop($var_one); 

    if ($this->page_size !== 0) $this->total_pages = ceil($this->total_rows/$this->page_size); 
     
    if ($this->cur_page > $this->total_pages) 
    { 
        $this->cur_page = $this->total_pages; 
        if ( $this->total_pages > 0 ) {
            $stn = $this->db->prepare( $this->query_paging($this->q));

                 try {
                    $stn->execute();
                } catch(PDOEcxeption $e){
                    logging(implode(",",$stn->errorInfo()),false,__FILE__,__LINE__);
                }
            $r = $stn->fetchAll();    
        } 
    } 
     
    return $r; 
} 

public function get_result_text() 
{ 
    $start = (($this->cur_page-1) * $this->page_size)+1; 
    $end = (($start-1+$this->page_size) >= $this->total_rows)? $this->total_rows:($start-1+$this->page_size); 

    return sprintf($this->result_text_pattern, $start, $end, $this->total_rows); 
} 

public function get_page_links() 
{ 
    if ( !isset($this->total_pages) ) return ''; 

    $page_link_list = array(); 

    $start = $this->cur_page - $this->link_padding; 
    if ( $start < 1 ) $start = 1; 
    $end = $this->cur_page + $this->link_padding-1; 
    if ( $end > $this->total_pages ) $end = $this->total_pages; 

    if ( $start > 1 )  $page_link_list[] = $this->get_page_link( $start-1, $start - 2 > 0 ? '...' : '' ); 
    for ($i=$start; $i <= $end; $i++)  $page_link_list[] = $this->get_page_link( $i ); 
    if ( $end + 1 < $this->total_pages ) $page_link_list[] = $this->get_page_link( $end +1, $end + 2 == $this->total_pages ? '' : '...' ); 
    if ( $end + 1 <= $this->total_pages ) $page_link_list[] = $this->get_page_link( $this->total_pages ); 

    return $page_link_list; 
} 

public function get_next_page_link() 
{ 
    return isset($this->total_pages) && $this->cur_page < $this->total_pages ? $this->get_page_link( $this->cur_page + 1, $this->next_page_text ) : ''; 
} 

public function get_prev_page_link() 
{ 
    return isset($this->total_pages) && $this->cur_page > 1 ? $this->get_page_link( $this->cur_page - 1, $this->prev_page_text ) : ''; 
} 

private function get_page_link($page, $text='') 
{ 
    if (!$text)    $text = $page; 

    if ($page != $this->cur_page) 
    { 
        $reg = '/((&|^)'.$this->page_var.'=)[^&#]*/'; 
        $url = '?'.( preg_match( $reg, $_SERVER['QUERY_STRING'] ) ? preg_replace($reg, '${1}'.$page, $_SERVER['QUERY_STRING']) : ( $_SERVER['QUERY_STRING'] ? $_SERVER['QUERY_STRING'].'&' : '' ).$this->page_var.'='.$page); 
        return array($url,$text);
    }
        return $text;         
 
} 

private function query_paging() 
{ 
    $q = $this->q; 

    if ($this->page_size != 0) 
    { 
        //calculate the starting row 
        $start = ($this->cur_page-1) * $this->page_size; 
        //insert SQL_CALC_FOUND_ROWS and add the LIMIT 
        $q = preg_replace('/^SELECT\s+/i', 'SELECT SQL_CALC_FOUND_ROWS ', $this->q)." LIMIT {$start},{$this->page_size}"; 
    } 

    return $q; 
} 

} 
?>