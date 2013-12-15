OOF
===
<div style="width: 400px; text-align: center">
***************NOTICE************************* <br />
This is still very much in development and not <br />
ready to run a site on. If you would like to <br />
help out there are many things I could use help <br />
with including fleshing out the cache a little <br />
more, building some helper classes to help with <br />
html, javascript, css, and whatever else. <br />
Feel free to ask. <br />
***********************************************<br />
</div>

##Installation and Configuraion
If you are looking for installation and configuration instructions you should head on over
to the <a href="http://gihub.com/danwguy/OOF/wiki">wiki</a> pages, everything you need is there.
For a fast and dirty version, here you go.

First step is to pull the repo to your server root directory. Next you are going to want to
open up the /application/config/site_config.php and update the information in the config array
to match your site setting, i.e. the database configuration and your prefered output method.
After that stuff is done, you are going to want to create a new database in your MySQL installation, named
the same as what you put into the config, and load in the framework.sql file, to setup the base tables for the
framework and the sample sites info. Once all that is done... go forth and build.

##Quick overview
A PHP framework that strives to make programming very complex applications a breeze. 
It is highly modeled after CodeIgniter. The main differences being with this framework 
you are always dealing with object regardless where you are in the code, the relational
mapping for your object are coded in a way that allows you to program relationships with
extreme ease and in any manner you want, and there are massive improvements on the database
interactions. There are autoloaders that allow you to never worry about using require or anything
else, there are multiple hooks throughout the entire framework allowing you to completely customize
the way the framework runs and handles object so you get to interact and code exactly how you want to
not how the framework tells you to, and there are tons of magic goodies throughout making extremely
complex relationships a breeze with functions like get_related, where Foo::get_related('Bar'); would
give you mapped out objects of related Bars that Foo needs, as well as a new Database interaction model
I call ARDO, or Automagic Relational Database Objects. A new way of making the most complex relationships
easily built and configured with a single call and zero extra work to the coder. Quick example is in your
class you would grab one of the many hooks before_construction and tell it what the related object is,
in Car objects before_construction method just write $this->relates[] = array('Car' => 'Tire');
and poof when you retrieve the Car object from the database it will automagically include the related
Tire object. Another helpful feature is the retrieve method, calling $car = new Car(1); will
give you the Car object from the database where the primary key = 1. Combine that with the ARDO
system and with a single line of code $car = new Car(1); you have your car obhect built from the
database and the related Tire objects, and not only is it all done in one line of code, but also in one
single query, seriously lowering the applications footprint giving you an extremely fast, extremely light
wieght application that builds out the most complex relationships in a snap. Give it a try, you know you want to.
 

 
