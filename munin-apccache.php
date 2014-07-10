<?php
/**
* Memory Size & Memory Available & Memory Used
* Cache Hits & Misses & Inserts
* Percents Memory & Percents Hits & Percents Misses
**/

$CONFIG_GRAPH = array(
    "memory"    => array( "title" => 'APC Cache Memory Usage', "vlabel" => 'memory'),
    "hits"      => array( "title" => 'APC Cache Hits', "vlabel" => 'hits'),
    "percents"  => array( "title" => 'APC Cache Percents', "vlabel" => 'percents')
);

$CONFIG_DATA_TYPES = array(
    "memory"    => array(   "memory_size.label" => 'APC Cache Memory Size',
                            "memory_size.min"   => '0',
                            "memory_size.draw"  => 'LINE2',

                            "memory_used.label" => 'APC Cache Memory Used',
                            "memory_used.min"   => '0',
                            "memory_used.draw"  => 'AREA',

                            "memory_free.label" => 'APC Cache Memory Available',
                            "memory_free.min"   => '0',
                            "memory_free.draw"  => 'STACK'
                        ),
    "hits"      => array(   "apccache_hits.label" => 'APC Cache Hits',
                            "apccache_hits.type"  => 'DERIVE',
                            "apccache_hits.min"   => '0',
                            "apccache_hits.draw"  => 'LINE2',

                            "apccache_misses.label" => 'APC Cache Misses',
                            "apccache_misses.type"  => 'DERIVE',
                            "apccache_misses.min"   => '0',
                            "apccache_misses.draw"  => 'LINE2',
                        ),
    "percents"  => array(   "hits.label" => 'APC Cache Hits Percents'
                            "hits.min"   => '0',
                            "hits.draw"  => 'AREA',

                            "misses.label"    => 'APC Cache Misses Percents'
                            "misses.min"      => '0',
                            "misses.warnings" => '50',
                            "misses.draw"     => 'STACK',

                            "memory.label"    => 'APC Cache Memory Percents'
                            "memory.min"      => '0',
                            "memory.warnings" => '95',
                            "memory.critical" => '98',
                            "memory.draw"     => 'LINE2',
                        )
);

$what = $_GET["what"];

if ( isset ( $_GET["config"] ) ) munin_apccache_print_config( $what );
else munin_apccache_print_data( $what );

function munin_apccache_check_type( $what )
{
    global $CONFIG_GRAPH;

    return ( isset( $CONFIG_GRAPH[$what] ) );
}

function munin_apccache_print_config( $what )
{
    global $CONFIG_GRAPH;
    global $CONFIG_DATA_TYPES;

    if ( ( !isset( $CONFIG_GRAPH[$what] ) ) || 
         ( !isset( $CONFIG_DATA_TYPES[$what] ) ) )
    {
        return false;
    }

    foreach ($CONFIG_GRAPH[$what] as $key => $value)
    {
        print "graph_".$key." ".$value."\n";
    }
    print "graph_category apccache\n";

    foreach ($CONFIG_DATA_TYPES[$what] as $key => $value)
    {
        print $key." ".$val."\n";
    }
}

function munin_apccache_print_data( $what )
{
    global $CONFIG_DATA_VALUES;

    if ( !isset( $CONFIG_DATA_VALUES[$what] ) )
    {
        return false;
    }

    $current = $CONFIG_DATA_VALUES[$what];
    $data = munin_apccache_get_data();

    // Data ?
    if ( $data === false )
    {
        foreach ($current as $key => $value)
        {
            print $key." U\n";
        }
        return false;
    }

    foreach ($current as $key => $value)
    {
        print $key.".value ".$data[$value]."\n";
    }

    return true;
}

function munin_apccache_get_data()
{
    $info['memory_size'] = 0;
    $info['memory_free'] = 0;
    $info['memory_used'] = 0;

    $info['apccache_misses'] = 0;
    $info['apccache_hits']   = 0;

    $info['hits']   = 0;
    $info['misses'] = 0;
    $info['memory'] = 0;

    //Get APC Cache statistics
    $status = apc_sma_info();

    $info['memory_size'] = $status['num_seg']*$status['seg_size'];
    $info['memory_free'] = $status['avail_mem'];
    $info['memory_used'] = $info['memory_size'] - $info['memory_free'];

    $info['apccache_misses'] = $status['num_misses'];
    $info['apccache_hits']   = $status['num_hits'];

    if ( $status['seg_size'] > 0 )
    {
        $info['memory'] = 100 - ( ( $status['avail_mem'] / $status['seg_size'] ) *100 );
    }
    
    if ( ( $status['num_hits'] + $status['num_misses'] ) > 0 ) 
    {
        $info['hits'] = ( $status['num_hits'] / ( $status['num_hits'] + $status['num_misses'] ) ) * 100;    
    }

    if ( ( $status["num_hits"] + $status["num_misses"] ) > 0 )
    {
        $info['misses'] = ( $status["num_misses"] / ( $status["num_hits"] + $status["num_misses"] ) ) * 100;
    }

    return $info;
}

?>