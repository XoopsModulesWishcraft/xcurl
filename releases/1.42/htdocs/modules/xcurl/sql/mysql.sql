
CREATE TABLE `curl_tables` (                 
      `tbl_id` int(20) unsigned NOT NULL auto_increment,  
      `tablename` varchar(220) default NULL,              
      `allowpost` tinyint(2) default '0',                 
      `allowretrieve` tinyint(2) default '0',             
      `allowupdate` tinyint(2) default '0',             
      `visible` tinyint(2) default '0',                   
      `view` tinyint(2) default '0',  
      PRIMARY KEY  (`tbl_id`)           
);

CREATE TABLE `curl_fields` (                 
      `fld_id` int(30) unsigned NOT NULL auto_increment,  
      `tbl_id` int(20) default '0',                       
      `key` tinyint(2) default '0',                       
      `fieldname` varchar(220) default NULL,              
      `allowpost` tinyint(2) default '0',                 
      `allowretrieve` tinyint(2) default '0',             
      `allowupdate` tinyint(2) default '0',             
      `visible` tinyint(2) default '0',                   
      `string` tinyint(2) default '0',                    
      `int` tinyint(2) default '0',                       
      `float` tinyint(2) default '0',                     
      `text` tinyint(2) default '0',                      
      `other` tinyint(2) default '0',
      `crc` tinyint(2) default '0',                                          
      PRIMARY KEY  (`fld_id`)           
);

CREATE TABLE `curl_plugins` (                   
      `plugin_id` int(10) unsigned NOT NULL auto_increment,  
      `plugin_name` varchar(255) default NULL,               
      `plugin_file` varchar(255) default NULL,               
      `active` tinyint(2) default '0',
      PRIMARY KEY  (`plugin_id`)                             
);