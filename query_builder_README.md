#ElasticSearch DSL Query Builder Class
This is a simple Query Builder for ElasticSearch DSL language

##Installation
It comes with the [php-es-mapper package](http://github.com/itvisionsy/php-es-mapper).
Read about it [here](./README.md).

##How to use
There are two ways to instantiate a builder object.

###Generic query builder
The query builder is not related to a specific TypeQuery class, it helps to build the query array and retrieve it. 
The built array will be then explicitly sent to some query class for execution.
```PHP
//init a builder object
$builder = \ItvisionSy\EsMapper\QueryBuilder::make();

//build the query using different methods
$query = $builder
                ->where('key1','some value') //term clause
                ->where('key2',$intValue,'>') //range clause
                ->where('key3','value','!=') //must_not term clause
                ->where('key4', ['value1','value2']) //terms clause
                ->where('email', '@hotmail.com', '*=') //wildcard search for all @hotmail.com emails
                ->sort('key1','asc') //first sort option
                ->sort('key2',['order'=>'asc','mode'=>'avg']) //second sort option
                ->from(20)->size(20) //results from 20 to 39
                ->toArray();

//modify the query as you need
$query['aggs']=['company'=>['terms'=>['field'=>'company']]];

//then execute it against a type query
$result = TypeQuery::query($query);
//$result is a \ItvisionSy\EsMapper\Result instance
```

###Auto implicit query builder
This type is implicitly created using a `TypeQuery::builder()` method.

It acts like the generic query builder, but in addition to everything the generic query builder can do, 
it allows you to execute the result query directly from within the query builder instant itself.

```PHP
$result = TypeQuery::builder()
    ->where('key1','some value') //term clause
    ->where('key2',$intValue,'>') //range clause
    ->where('key3','value','!=') //must_not term clause
    ->where('key4', ['value1','value2']) //terms clause
    ->where('email', '@hotmail.com', '*=') //wildcard search for all @hotmail.com emails
    ->sort('key1','asc') //first sort option
    ->sort('key2',['order'=>'asc','mode'=>'avg']) //second sort option
    ->from(20)->size(20) //results from 20 to 39
    ->execute();
//$result is a \ItvisionSy\EsMapper\Result instance
```

##Building a query

###The available ElasticSearch methods
The query builder allows the main filter/query clauses: `term`, `match`, `wildcard`, `range`, and `prefix`.
Each of these methods can be used against a key, or multiple keys, or a value, or multiple values, giving
flexible way to build up a really complex queries.

###The smart `where` method
The `::where($key, $value, $compare, $filter)` method is also provided, which will scan, detect, and convert
itself to one of the previous available methods. You can use the $compare to tell it what you are looking for,
and the $filter to enforce a filter or query clause.

###Sorting
The `::sort($key, $order)` allows you to add multiple sort levels. 

The `$order` parameter can be a simple 'desc' or 'asc' value, or can be an assoc array as per ElasticSearch [sort documentation](https://www.elastic.co/guide/en/elasticsearch/reference/1.6/search-request-sort.html).

The third `$override` parameter allows you to clear the sort section before adding the sort terms.


###Pagination
You can use `from`, and/or `size` for detailed control, or use the `page($size, $from)` which will make two different calls to each method alone. 

###Extra methods
If you need to add extra clauses, there are several methods which allow you to: 
`raw`, `rawMustFilter`, `rawMustQuery`, `rawMustNotFilter`, `rawMustNotQuery`, `rawShouldFilter`, and `rawShouldQuery`.

The raw query allows you to add any query/filter clause as an assoc-array to one of the filter/query [bool](https://www.elastic.co/guide/en/elasticsearch/reference/1.6/query-dsl-bool-query.html) clause sections.

###Sub queries

Sub queries will be automatically created when needed. However, you can add subqueries using the `andWhere` and/or `orWhere` methods.

Each of the two methods will start a new subquery, and returns a new query builder object of class `\ItvisionSy\EsMapper\SubQueryBuilder` where you can add extra filter clauses as you want.

When you are done with the subquery, you can finish it and return to the main query by calling the `->endSubQuery()` method.

For now, the subqueries can only be added to the filter bool clause sections.

##Finish the query

###Get the final query array/JSON
Once done, call the `->toArray()` or `->toJSON()` to get the final query array or JSON string.

###Empty the query
You can also empty the query and start a new query. This is handy if you have a main base query and you want to execute it multiple time with some minor changes.
```PHP
$baseQuery = [
    'query'=>[
        'filtered'=>[
            'filter'=>[
                'bool'=>[
                    //some complex base query
                ]
            ]
        ]
    ]
];
$builder = CustomersTypeQuery::builder($baseQuery);
$page1 = $builder->page(10,0)->execute();
$page2 = $builder->emptyQuery($baseQuery)->page(10,10)->execute();
```
This is just a sample to show you how the empty query works. You can come up with different use cases and scenarios.

###Execute the query
If the main query builder is instantiated from within a `TypeQueryInterface` compliant query class, then you can execute the query directly using the `->execute()` method.

This method is going to call the instanciator TypeQuery `query` method passing the final query array as a parameter.

##Sample
```PHP
$result = CustomersQuery::builder()                 //all customers
            ->where('email','@hotmail.com', '*=')   //who have hotmail.com emails
            ->where(['created','updated'],['today','yesterday'])  //and were active today or yesterday
            ->orWhere()                                 //starts a subquery
                ->where('country',['UAE','KSA','TUR'])  //where country is UAE, KSA, or TUR
                ->where('age', [10,20], '<=>=')         //or the age is between 10 and 20 years
            ->endSubQuery()                             //ends the sub query to the main query
            ->rawMustNotFilter([                        //add a raw nested must-not filter
                'nested'=>[
                    'path'=>'visits',
                    'filter'=>[
                        'term'=>['ip'=>'192.168.0.5']
                    ]
                ]
            ])                                          //where none of their visits from a specific ip
            ->sort('updated','desc')                    //most recent first
            ->page(10, 20)                              //10 results starting from 20
            ->execute();
```