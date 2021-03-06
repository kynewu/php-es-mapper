<?php

/**
 * Copyright (c) 2015, Muhannad Shelleh
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
 * THE SOFTWARE.
 */

namespace ItvisionSy\EsMapper;

$errorReporting = error_reporting();
error_reporting(E_ALL & ~(E_NOTICE | E_DEPRECATED | E_STRICT));

/**
 * This class is to narrow down a generic index-level query class operations to 
 * one specific index type.
 * 
 * All the generic operations in the generic class are applicable here, however,
 * they omit the type parameter and will pass it internally to the index-level
 * class methods when called.
 *
 * @package ItvisionSy\EsMapper
 * @author Muhannad Shelleh <muhannad.shelleh@itvision-sy.com>
 * 
 */
abstract class TypeQuery extends Query implements TypeQueryInterface {

    use TypeQueryTrait;
}

error_reporting($errorReporting);
