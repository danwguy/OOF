-- phpMyAdmin SQL Dump
-- version 3.5.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 07, 2013 at 02:10 AM
-- Server version: 5.5.24-log
-- PHP Version: 5.3.13

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `framework`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_id`) VALUES
(1, 'Electronics', 0),
(2, 'Toys & Games', 0),
(3, 'Camera', 1),
(4, 'Security', 1),
(5, 'Games', 2),
(6, 'Puzzles', 2);

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE IF NOT EXISTS `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT '0',
  `coming_soon` tinyint(1) NOT NULL DEFAULT '1',
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `play_page` varchar(255) DEFAULT NULL,
  `about` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `name`, `active`, `coming_soon`, `description`, `image`, `play_page`, `about`) VALUES
(1, 'Simon', 1, 0, ' A memory testing game made popular in the 80''s.\r\n            Today many online ports of this game exist, however\r\n            most of the ones I''ve seen are in flash and ...\r\n\r\n            really?... flash?... No thank you!\r\n            So I made one in JavaScript so everyone can play,\r\n            since Android doesn''t use flash, and really no one\r\n            should.\r\n            Enjoy!', 'psymon_small.png', 'simon.php', 'This is a remake of the original Simon game made back in the 80''s. It is still a memory based game where colors will light up in random order and you must hit the same colors in the same order before time runs out, but this one has a twist.\r\nThere are game modes to make it a bit more challenging. Bored of playing the normal one because you can get too far and there''s no perceived challenge?\r\nStep up to medium and watch the game get steadily faster and faster. Still not exciting enough?\r\nPlay on hard core and really test your memory and speed. Hard core is designed to get insanely fast while making the time the colors light up less and less. Good luck!');

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE IF NOT EXISTS `items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `item_name`) VALUES
(1, 'Get Milk'),
(2, 'Buy Application'),
(3, 'Kill Comcast'),
(4, 'Fuck like Rabbits');

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `relates_to` varchar(255) DEFAULT NULL,
  `mode` enum('uninitialized','live','deleted','suggested_for_deletion','unknown') NOT NULL DEFAULT 'live',
  `status` enum('active','ended','inactive','archived') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id`, `name`, `relates_to`, `mode`, `status`) VALUES
(1, 'javascript', 'web_programming', 'live', 'active'),
(2, 'php', 'web_programming', 'live', 'active'),
(3, 'css', 'web_programming', 'live', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE IF NOT EXISTS `menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) DEFAULT NULL,
  `mode` enum('uninitialized','live','deleted','suggested_for_deletion','unknown') NOT NULL DEFAULT 'live',
  `status` enum('active','ended','inactive','archived') NOT NULL DEFAULT 'active',
  `link` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id`, `title`, `mode`, `status`, `link`) VALUES
(1, 'Home', 'live', 'active', 'home'),
(2, 'Resume', 'live', 'active', 'resume'),
(3, 'Games', 'live', 'active', 'games'),
(4, 'Tips & Tricks', 'live', 'active', 'tips_tricks'),
(5, 'Contact', 'live', 'active', 'contact');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `content` text,
  `linked_menu` int(11) DEFAULT NULL,
  `mode` enum('uninitialized','live','deleted','suggested_for_deletion','unknown') NOT NULL DEFAULT 'live',
  `status` enum('active','ended','inactive','archived') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `pages`
--

INSERT INTO `pages` (`id`, `content`, `linked_menu`, `mode`, `status`) VALUES
(1, '<div class="inner-content">\r\n<h1>Welcome</h1>\r\n<p>Hello there and welcome to my little slice of the web. This site is constantly undergoing upgrades and getting new content, so be sure to check back often. A little about me, I am a senior level/lead programmer. I specialize in web based programming and truly live for live, rich, full feedback websites that customers are happy to visit. I specialize in single page applications while using very thin client programming methods to keep speedy responsive sites.</p><br />\r\n<img class=''content-divider'' src=''img/content_divider.png'' />\r\n<h2>What''s on this page?</h2>\r\n<p>On this site you will find a plethora of information. There are games to play, with new ones being added all the time. There is a Tips and Tricks section that will have all kinds of useful... well... Tips and Tricks for various web based languages, I try to add new entries every time I encounter a situation that I think will be helpful to people, so check back often for new entries. You can also email me suggestions or questions and I will try to get a post up for you asap. You can view my resume and hire me... what can I say I dream big. Last but not least a contact information, because, well, really how could you hire me without my contact info? So take a look around, play a game or two, maybe learn a little something over in the Tips and Tricks are, or just shoot me an email to tell me how good of a site it really is ;). I am always open to feedback so good, bad, or ugly, feel free to drop me a line. Now, go forth and enjoy.</p>\r\n<div class="info">\r\n<img class=''bio-image'' src=''public/img/bio-pic2.png'' />\r\n<h1>Info</h1>\r\n<ul>\r\n<li>Name: <span class="red">Robert Mason</span></li>\r\n<li>Age: <span class="red">32</span></li>\r\n<li>Home Town: <span class="red">Beaverton</span></li>\r\n<li>Occupation: <span class="red">Web Developer</span></li>\r\n<li>Company: <span class="red">None right now</span></li>\r\n<li>E-Mail: <span class="red">danwguy@gmail.com</span></li>\r\n</ul>\r\n</div>\r\n</div>', 1, 'live', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE IF NOT EXISTS `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`id`, `position`, `description`) VALUES
(1, 'user', 'Default system user, no privileges '),
(2, 'admin', 'Elevated privileges, able to add, edit, create, and delete posts');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE IF NOT EXISTS `products` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `price`) VALUES
(1, 3, 'Product A', '34'),
(2, 3, 'Product B', '40'),
(3, 4, 'Product C', '50'),
(4, 4, 'Product D', '50'),
(5, 5, 'Product E', '44'),
(6, 5, 'Product F', '55'),
(7, 6, 'Product G', '45'),
(8, 6, 'Product H', '23');

-- --------------------------------------------------------

--
-- Table structure for table `products_tags`
--

CREATE TABLE IF NOT EXISTS `products_tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `tag_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `products_tags`
--

INSERT INTO `products_tags` (`id`, `product_id`, `tag_id`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 2, 3),
(5, 3, 4),
(6, 4, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(45) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` (`id`, `name`) VALUES
(1, 'Tag A'),
(2, 'Tag B'),
(3, 'Tag C'),
(4, 'Tag D');

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `themes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `css_class` varchar(100) DEFAULT NULL,
  `mode` enum('uninitialized','live','deleted','suggested_for_deletion','unknown') NOT NULL DEFAULT 'live',
  `status` enum('active','ended','inactive','archived') NOT NULL DEFAULT 'active',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id`, `title`, `description`, `css_class`, `mode`, `status`) VALUES
(1, 'Minimalism is good', 'gray and white minimalism theme', 'city', 'live', 'active'),
(2, 'Transparency is beautiful', 'Dark and transparent theme', 'transparent', 'live', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `tips_tricks`
--

CREATE TABLE IF NOT EXISTS `tips_tricks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `language` varchar(100) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `mode` enum('uninitialized','live','deleted','suggested_for_deletion','unknown') NOT NULL DEFAULT 'live',
  `status` enum('active','ended','inactive','archived') NOT NULL DEFAULT 'active',
  `created_on` datetime DEFAULT NULL,
  `modified_on` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `tips_tricks`
--

INSERT INTO `tips_tricks` (`id`, `language`, `title`, `content`, `mode`, `status`, `created_on`, `modified_on`) VALUES
(1, '1', 'Memory saving tricks when building in-depth javascript applications', 'This would be the content area where you will add content to the subject. specialized tags are usable here such as\r\n\r\n<pre class="prettyprint">\r\nvar contentDiv = {\r\n    elementHeight : ''100px'',\r\n    element : function() {\r\n        return $(''.hidden-content'');\r\n    },\r\n    toggleElement : function() {\r\n        if(this.element().is('':visible'')) {\r\n            this.hideElement();\r\n        } else {\r\n            this.showElement();\r\n        }\r\n        return this;\r\n    },\r\n    toggleContent : function(content) {\r\n        this.hideElement();\r\n        this.add(content);\r\n        this.showElement();\r\n    },\r\n    showElement : function() {\r\n        var that = this;\r\n        this.element()\r\n            .css({''height'':0, ''overflow'':''hidden''})\r\n            .show()\r\n            .animate({''height'': that.elementHeight}, 500);\r\n        return this;\r\n    },\r\n    hideElement : function() {\r\n        this.element()\r\n            .animate({''height'' : 0}, 500, function() { $(this).hide();});\r\n        return this;\r\n    },\r\n    add : function(content) {\r\n        this.element().html(content);\r\n        return this;\r\n    },\r\n    animateTo : function(obj) {\r\n        this.element().animate(obj);\r\n        return this;\r\n    }\r\n}\r\n\r\nvar page = {\r\n    reply : null,\r\n    getPage : function(obj) {\r\n        var that = this;\r\n        $.ajax({\r\n            async: false,\r\n            type: "POST",\r\n            url: "controller/controller.php",\r\n            data: obj,\r\n            success: function(msg) {\r\n                that.reply = msg;\r\n            }\r\n       })\r\n        return this.reply;\r\n    },\r\n    fillHiddenContent : function(content) {\r\n        contentDiv.add(content).toggleElement();\r\n    }\r\n}\r\n</pre> \r\nand so on for other languages. It is recommended to wrap all  tags in a  tag to keep spacing and layout.', 'live', 'active', '2013-07-01 00:00:00', '0000-00-00 00:00:00'),
(2, '1', 'Test Post #2 Here is the title for it', 'This is the content for test post #2 in JavaScript. These are testing posts that will be replaced by actual content from a real post ', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(3, '1', 'Test Post #3 Here is the title for it', 'This is the content for test post #3 in JavaScript. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(4, '2', 'Test Post #1 Here is the title for it', 'This is the content for test post #1 in PHP. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(5, '2', 'Test Post #2 Here is the title for it', 'This is the content for test post #2 in PHP. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(6, '2', 'Test Post #3 Here is the title for it', 'This is the content for test post #3 in PHP. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(7, '3', 'Test Post #1 Here is the title for it', 'This is the content for test post #1 in CSS. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(8, '3', 'Test Post #2 Here is the title for it', 'This is the content for test post #2 in CSS. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(9, '3', 'Test Post #3 Here is the title for it', 'This is the content for test post #3 in CSS. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(10, '3', 'Test Post #3 Here is the title for it', 'This is the content for test post #3 in CSS. These are testing posts that will be replaced by actual content from a real post', 'live', 'active', NULL, '0000-00-00 00:00:00'),
(11, '1', 'Created with the editor', '<p class="test-text"><span style="line-height: 1;">Input your post here... Here is a sample post created with the editor</span></p>\r\n<p class="test-text"><pre class="prettyprint"></p>\r\n<p class="test-text">&nbsp; &nbsp; var testData = {</p>\r\n<p class="test-text">&nbsp; &nbsp; &nbsp; &nbsp; something: ''something else''</p>\r\n<p class="test-text">&nbsp; &nbsp; }</p>\r\n<p class="test-text"></pre></p>', 'live', 'active', '2013-07-25 14:41:04', '2013-07-25 21:41:04');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `title` int(11) DEFAULT NULL,
  `pass_hash` varchar(255) DEFAULT NULL,
  `ip_address` varchar(100) DEFAULT NULL,
  `session_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `email`, `first_name`, `last_name`, `title`, `pass_hash`, `ip_address`, `session_id`) VALUES
(1, 'danwguy@gmail.com', 'Robert', 'Mason', 2, 'f3bbbd66a63d4bf1747940578ec3d0103530e21d', '::1', 'rq5ajajugfkv5335jo299l3m52');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
