<?php
/**
* Cached Keys & Free Keys
* Cache Hits & Misses
* Used Memory & Free Memory & Wasted Memory
**/

$CONFIG_GRAPH = array (
    "keys"      => array( "title" => 'OPCache Keys', "vlabel" => 'keys' ),
    "hits"      => array( "title" => 'OPCache Hits', "vlabel" => 'hits' ),
    "memory"    => array( "title" => 'OPCache Memory Usage', "vlabel" => 'memory')
);

$CONFIG_DATA_TYPES = array (
    "keys"      => array (  "cached_keys.label" => 'Number of Cached Keys',
                            "free_keys.label"   => 'Number of Keys Available',
                        ),
    "hits"      => array (  "opcache_hits.label" => 'OPCache Hits',
                            "opcache_hits.type"  => 'DERIVE',
                            "opcache_hits.min"   => '0',
                            "opcache_hits.draw"  => 'LINE2',

                            "opcache_misses.label" => 'OPCache Misses',
                            "opcache_misses.type"  => 'DERIVE',
                            "opcache_misses.min"   => '0',
                            "opcache_misses.draw"  => 'LINE2',
                        ),
    "memory"    => array (  "used_memory.label"   => 'OPCache Memory Used',
                            "free_memory.label"   => 'OPCache Memory Available',
                            "wasted_memory.label" => 'OPCache Memory Wasted',
                        )
);

$CONFIG_DATA_VALUES = array (
    "keys"      => array (  "cached_keys" => 'cached_keys',
                            "free_keys"   => 'free_keys',
                        ),
    "hits"      => array (  "opcache_hits"   => 'opcache_hits',
                            "opcache_misses" => 'opcache_misses',
                        ),
    "memory"    => array (  "used_memory"   => 'used_memory',
                            "free_memory"   => 'free_memory',
                            "wasted_memory" => 'wasted_memory',
                        )
);

$what = $_GET["what"];

if ( isset ( $_GET["config"] ) ) munin_opcache_print_config( $what );
else munin_opcache_print_data( $what );

function munin_opcache_check_type( $what )
{
    global $CONFIG_GRAPH;

    return ( isset( $CONFIG_GRAPH[$what] ) );
}

function munin_opcache_print_config( $what )
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
    print "graph_category opcache\n";

    foreach ($CONFIG_DATA_TYPES[$what] as $key => $value)
    {
        print $key." ".$val."\n";
    }
}

function munin_opcache_print_data( $what )
{
    global $CONFIG_DATA_VALUES;

    if ( !isset( $CONFIG_DATA_VALUES[$what] ) )
    {
        return false;
    }

    $current = $CONFIG_DATA_VALUES[$what];
    $data = munin_opcache_get_data();

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

function munin_opcache_get_data()
{
    $info['cached_keys'] = 0;
    $info['free_keys']   = 0;

    $info['opcache_hits']   = 0;
    $info['opcache_misses'] = 0;

    $info['used_memory']   = 0;
    $info['free_memory']   = 0;
    $info['wasted_memory'] = 0;

    //Get OPCache statistics
    $status = opcache_get_status();

    $info['cached_keys'] = $status['opcache_statistics']['num_cached_keys'];
    $info['free_keys']   = $status['opcache_statistics']['max_cached_keys'] - $status['opcache_statistics']['num_cached_keys'];

    $info['opcache_hits']   = $status['opcache_statistics']['hits'];
    $info['opcache_misses'] = $status['opcache_statistics']['misses'];

    $info['used_memory']   = sprintf("%.2f", $status['memory_usage']['used_memory'] / 1048576);
    $info['free_memory']   = sprintf("%.2f", $status['memory_usage']['free_memory'] / 1048576);
    $info['wasted_memory'] = sprintf("%.2f", $status['memory_usage']['wasted_memory'] / 1048576);

    return $info;
}

?>