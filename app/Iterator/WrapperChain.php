<?php

/**
 * Chain of wrappers.
 *
 * PHP Version 7.1
 *
 * @category File
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/ Source Code
 */

namespace CrudeForum\CrudeForum\Iterator;
use \Exception;
use \Iterator;

/**
 * Chain of wrappers.
 *
 * @category Class
 * @package  CrudeForum\CrudeForum\Iterator
 * @author   Koala Yeung <koalay@gmail.com>
 * @license  https://opensource.org/licenses/MIT MIT License
 * @link     https://github.com/crude-forum/crude-forum/ Source Code
 */
class WrapperChain
{

    private $_wrapper0 = null;
    private $_wrappers = [];
    /**
     * Class constructor
     *
     * @param Wrapper ...$wrappers Array of wrapper from inner to outer.
     */
    public function __construct(Wrapper ...$wrappers)
    {
        if (sizeof($wrappers) <= 0) throw new Exception('WrapperChain requires to have more than 1 Wrapper');
        $this->_wrapper0 = array_shift($wrappers);
        $this->_wrappers = $wrappers;
    }

    /**
     * To wrap an Iterator into the current object.
     *
     * @param Iterator $iter An iterator to wrap with.
     *
     * @return Wrapper
     */
    public function &wrap(Iterator $iter): Wrapper
    {
        $output = $this->_wrapper0->wrap($iter);
        foreach ($this->_wrappers as $key => $wrapper) {
            $output = $wrapper->wrap($output);
        }
        return $output;
    }
}