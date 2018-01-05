.<?php

use CrudeForum\CrudeForum\Filter;

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
});