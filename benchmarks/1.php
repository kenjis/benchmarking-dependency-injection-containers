<?php

echo PHP_EOL . PHP_EOL;

require __DIR__ . '/../vendor/autoload.php';

// To stop autoloader caching skewing results
$bart = new Benchmark\Stubs\Bart;
$bam = new Benchmark\Stubs\Bam($bart);
$baz = new Benchmark\Stubs\Baz($bam);
$bar = new Benchmark\Stubs\Bar($baz);
$foo =  new Benchmark\Stubs\Foo($bar);

unset($foo);
unset($bar);
unset($baz);
unset($bam);
unset($bart);

function check($foo)
{
    if (! $foo->bar->baz->bam->bart instanceof Benchmark\Stubs\Bart) {
        var_dump($foo); exit;
    }
}


$bm = new Benchmark\Timer;

/*******************************************************************************
 Benchmark 1: Auto resolution of object and dependencies.
 (Aliasing Interfaces to Concretes)
 Excluded: Pimple, Symfony
********************************************************************************/

for ($i = 0; $i < 1000; $i++) {

    // Illuminate\Container (Laravel)
    $illuminate = new Illuminate\Container\Container;
    $bm->start('benchmark1', 'laravel');
    $illuminate->bind('Foo', 'Benchmark\Stubs\Foo');
    $illuminate->bind('Benchmark\Stubs\BazInterface', 'Benchmark\Stubs\Baz');
    $illuminate->bind('Benchmark\Stubs\BartInterface', 'Benchmark\Stubs\Bart');
    $foo = $illuminate->make('Foo');
    check($foo);
    $bm->end('benchmark1', 'laravel');
    unset($illuminate);
    unset($foo);

}

//for ($i = 0; $i < 1000; $i++) {
//
//    // Orno\Di
//    $orno = new Orno\Di\Container;
//    $bm->start('benchmark1', 'orno');
//    $orno->add('Benchmark\Stubs\BazInterface', 'Benchmark\Stubs\Baz');
//    $orno->add('Benchmark\Stubs\BartInterface', 'Benchmark\Stubs\Bart');
//    $foo = $orno->get('Benchmark\Stubs\Foo');
//    check($foo); // @TODO NG
//    $bm->end('benchmark1', 'orno');
//    unset($orno);
//    unset($foo);
//
//}

for ($i = 0; $i < 1000; $i++) {

    // League\Di
    $league = new League\Di\Container;
    $bm->start('benchmark1', 'league');
    $league->bind('Benchmark\Stubs\BazInterface', 'Benchmark\Stubs\Baz');
    $league->bind('Benchmark\Stubs\BartInterface', 'Benchmark\Stubs\Bart');
    $foo = $league->resolve('Benchmark\Stubs\Foo');
    check($foo);
    $bm->end('benchmark1', 'league');
    unset($league);
    unset($foo);

}

//for ($i = 0; $i < 1000; $i++) {
//
//    // Zend\Di
//    $zend = new Zend\Di\Di;
//    $bm->start('benchmark1', 'zend');
//    $zend->instanceManager()->addTypePreference('Benchmark\Stubs\BazInterface', 'Benchmark\Stubs\Baz');
//    $zend->instanceManager()->addTypePreference('Benchmark\Stubs\BartInterface', 'Benchmark\Stubs\Bart');
//    $foo = $zend->get('Benchmark\Stubs\Foo');
//    check($foo); // @TODO NG
//    $bm->end('benchmark1', 'zend');
//    unset($zend);
//    unset($foo);
//
//}

for ($i = 0; $i < 1000; $i++) {

    // PHP-DI
    $builder = new DI\ContainerBuilder();
    $bm->start('benchmark1', 'php-di');
    $builder->useAnnotations(false);
    $phpdi = $builder->build();
    $phpdi->set('Benchmark\Stubs\BazInterface', DI\object('Benchmark\Stubs\Baz'));
    $phpdi->set('Benchmark\Stubs\BartInterface', DI\object('Benchmark\Stubs\Bart'));
    $foo = $phpdi->get('Benchmark\Stubs\Foo');
    check($foo);
    $bm->end('benchmark1', 'php-di');
    unset($phpdi);
    unset($foo);

}

for ($i = 0; $i < 1000; $i++) {

    // Dice
    $dice = new Jasrags\Dice;
    $bm->start('benchmark1', 'dice');
    $rule = new Jasrags\Dice\Rule;
    $rule->substitutions['Benchmark\Stubs\BazInterface'] = new Jasrags\Dice\Instance('Benchmark\Stubs\Baz');
    $dice->addRule('Benchmark\Stubs\Bar', $rule);
    $rule = new Jasrags\Dice\Rule;
    $rule->substitutions['Benchmark\Stubs\BartInterface'] = new Jasrags\Dice\Instance('Benchmark\Stubs\Bart');
    $dice->addRule('Benchmark\Stubs\Bam', $rule);
    $foo = $dice->create('Benchmark\Stubs\Foo');
    check($foo);
    $bm->end('benchmark1', 'dice');
    unset($dice, $rule);
    unset($foo);

}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Benchmark 1</title>

    <meta name="viewport" content="width-device-width, initial-scale=1">
</head>
<body>
    <div id="chart_div" style="width: 620px; height: 400px;"></div>

    <script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            ['Component', 'Time Taken'],
            ['Illuminate\\Container', <?= $bm->getBenchmarkTotal('benchmark1', 'laravel') ?>],
            ['League\\Di', <?= $bm->getBenchmarkTotal('benchmark1', 'league') ?>],
            ['PHP-DI', <?= $bm->getBenchmarkTotal('benchmark1', 'php-di') ?>],
            ['Dice', <?= $bm->getBenchmarkTotal('benchmark1', 'dice') ?>]
        ]);

        var options = {};

        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
    </script>
</body>
</html>
