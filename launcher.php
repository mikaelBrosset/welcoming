<?php

// to run it with cli -> php launcher.php path1 path2
// ex : php launcher.php /var/www/html/welcoming/random.csv /var/www/html/welcoming/random2.csv

// to run it with a browser -> path/to/file/launcher.php?f1=path1&f2=path2
// ex : http://localhost/welcoming/launcher.php?f1=/var/www/html/welcoming/random.csv&f2=/var/www/html/welcoming/random2.csv

if (php_sapi_name() === 'cli') {
    $f1 = $argv[1];
    $f2 = $argv[2];

    $a1 = file($f1);
    $a2 = file($f2);

    $intersect = array_intersect($a1, $a2);
    foreach ($intersect as $i) {
        echo $i.PHP_EOL;
    }
} else {
    $f1 = $_GET['f1'];
    $f2 = $_GET['f2'];
}
$i1 = new SplFileObject($f1);
$i2 = new SplFileObject($f2);

$limit = 5000;

$start1 = $start2 = 0;
$end1 = $end2 = $limit;
$total1 = $total2 = 0;

foreach ($i1 as $i) {
    $total1++;
}
foreach ($i2 as $i) {
    $total2++;
}

$finalIntersect = new ArrayIterator([]);

ext($i1, $i2, $total1, $total2, $start1, $start2, $end1, $end2, $limit, $finalIntersect);

function ext($i1, $i2, &$total1, &$total2, &$start1, &$start2, &$end1, &$end2, $limit, &$finalIntersect)
{
    while ($start1 <= $total1) {
        $chunk1 = chunk($i1, $start1, $end1, $limit);
        $start2 = 0;
        $end2 = $limit;
        while ($start2 <= $total2) {
            $chunk2 = chunk($i2, $start2, $end2, $limit);
            compare($chunk1, $chunk2, $finalIntersect);
            gc_collect_cycles();
        }
        gc_collect_cycles();
    }
}

function chunk($it, &$start, &$end, $limit)
{
    $count = 0;
    $a = [];
    foreach ($it as $i) {
        if ($count >= $start && $count < $end) {
            $a[] = $i;
            $count++;
        } elseif ($count >= $end) {
            break;
        } else {
            continue;
        }
    }

    $start += $limit;
    $end += $limit;

    return $a;
}

function compare($chunk1, $chunk2, &$finalIntersect)
{
    $intersects = array_intersect($chunk1, $chunk2);
    if ($intersects) {
        $intersects = array_unique(flatten($intersects));
        foreach ($intersects as $intersect) {
            $finalIntersect->append($intersect);
        }
    }
}

function flatten(array $array)
{
    $return = array();
    array_walk_recursive(
        $array,
        function ($a) use (&$return) {
            $return[] = $a;
        }
    );

    return $return;
}

foreach ($finalIntersect as $inter) {
    echo $inter.PHP_EOL;
}
