OOF
===

***************NOTICE*************************
This is still very much in development and not
ready to run a site on. If you would like to
help out there are many things I could use help
with including fleshing out the cache a little
more, building some helper classes to help with
html, javascript, css, and whatever else.
Feel free to ask.
***********************************************


A PHP framework that strives to make programming very complex applications a breeze. 
It is highly modeled after CodeIgniter. The main difference being with this framework 
you are always dealing with object regardless where you are in the code. 
There are also HUGE improvements to database interactions. I have worked overtime making 
all kinds of goodies including making relational abject extremely simple with multiple ways 
to achieve the same goal. This framework really focuses on flexibility in coding styles.
One of the ways this is achieved is through options, allowing you to code the relationship 
between objects the way you want to. you are free to use the very typical and popular method
of using $this->hasMany, $this->hasOne, $this->hasManyAndBelongsToMany, or if you prefer, as I do,
to make sure that in your database if foo has 1 or many bar(s) then bar has foo_id in it then you can
use the magic of get_related. Here is a quick example.
Database:
--------------------------------------
|table: cars                          |
--------------------------------------
| id  | make  | model | color |       |
--------------------------------------
| 1   | Ford  | F-150 | Black |       |
--------------------------------------

--------------------------------------
| table: tires                        |
--------------------------------------
| id  | brand_name  | size  | cars_id |
--------------------------------------
| 1   | Goodyear    | 32x30 | 1       |
--------------------------------------
//... and so on
PHP:
  class Car extends TableObject {
      public $id;
      public $make;
      public $model;
      public $color;
      public $Tire; //becase this will have a ->Tire relation
      
      public static $column_list = array('id', 'make', 'model', 'color'); //not necessary, but saves a query so good practice
      
      //... and so on for your class
  }
  //The Tire class is pretty much the exact same
Now in your controller or wherever else you want this info...
$car = new Car(1); //retrieves the car object from the database with an id = 1
$car->Tire = Car::get_related('Tire');
you now have...
Car Object (
  [id] => 1,
  [make] => 'Ford',
  [model] => 'F-150',
  [color] => 'Black',
  [Tire] => Tire Object (
    //... and here is the tire class

But wait, there is yet a third option!! 
As far as I know this is a programming debut.
A new method you can enable in your config called ADRO,
or Automagic Database Relational Objects, yeah it's a cool name
using the exact same database structure above you can automagically
get all the object, completely related in one line and one query...

$car = new Car(1);

Yep... it really is that simple. The output is the same, a car object
with Car->Tire being a Tire object, or an array of Tire objects if it
is a 1 -> many relationship.
There is only 2 things needed to use this awesome new magic,
1. Enable ARDO in the site_config in the database section just enable .... 'ARDO' => true
2. Use the built in hooks to define the relationship...
  class Car extends TableObject
    public $id;
    //,... you get it
    
    public function before_construction() {
        $this->relates[] = array('Car' => 'Tire');
    }
and that is that... sweet eh? There are many examples in the repo

 

 
