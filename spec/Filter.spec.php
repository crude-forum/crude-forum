.<?php

use CrudeForum\CrudeForum\Filter;
use \Phata\Widgetfy\Core as Widgetfy;

describe('CrudeForum\CrudeForum\Filter', function () {
    describe('::reduceFlashEmbed', function () {
        it('pass if can filter Youtube embed sample 1', function () {
            $input = '<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/WiGCOm8Bkco&hl=zh_TW&fs=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/WiGCOm8Bkco&hl=zh_TW&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="425" height="344"></embed></object>';
            $output = Filter::pipeToString(Filter::reduceFlashEmbed(Filter::stringToPipe($input)));
            expect($output)->toBe('http://www.youtube.com/v/WiGCOm8Bkco&hl=zh_TW&fs=1');
        });
        it('pass if can filter Youtube embed sample 2', function () {
            $input = "Hello World\nfoobar " . '<object width="425" height="344"><param name="movie" value="http://www.youtube.com/v/WiGCOm8Bkco&hl=zh_TW&fs=1"></param><param name="allowFullScreen" value="true"></param><embed src="http://www.youtube.com/v/WiGCOm8Bkco&hl=zh_TW&fs=1" type="application/x-shockwave-flash" allowfullscreen="true" width="425" height="344"></embed></object>' . " Some more hello";
            $output = Filter::pipeToString(Filter::reduceFlashEmbed(Filter::stringToPipe($input)));
            expect($output)->toBe("Hello World\nfoobar http://www.youtube.com/v/WiGCOm8Bkco&hl=zh_TW&fs=1 Some more hello");
        });
    });

    describe('::autoWidgetfy', function () {
        it('pass if can filter Youtube URL sample 1', function () {
            $input = "Hello world\nhttps://www.youtube.com/watch?v=Ycf1fcom6So\nThats all\n";
            $output = Filter::pipeToString(Filter::autoWidgetfy(Filter::stringToPipe($input)));
            $expected = "Hello world\n" . Widgetfy::translate('https://www.youtube.com/watch?v=Ycf1fcom6So')['html'] . "\nThats all\n";
            expect($output)->toBe($expected);
        });
        it('pass if can filter Youtube URL sample 2', function () {
            $input = "Hello world\nhttp://www.youtube.com/v/WiGCOm8Bkco\nThats all\n";
            $output = Filter::pipeToString(Filter::autoWidgetfy(Filter::stringToPipe($input)));
            $expected = "Hello world\n" . Widgetfy::translate('https://www.youtube.com/watch?v=WiGCOm8Bkco&hl=zh_TW&fs=1')['html'] . "\nThats all\n";
            expect($output)->toBe($expected);
        });

    });
});