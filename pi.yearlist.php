<?php
/*
================================================================
	Year List
	for EllisLab ExpressionEngine - by Ryan Irelan	
----------------------------------------------------------------
	Copyright (c) 2008 Ryan Irelan
================================================================
	THIS IS COPYRIGHTED SOFTWARE. PLEASE
	READ THE LICENSE AGREEMENT.
----------------------------------------------------------------
	This software is based upon and derived from
	EllisLab ExpressionEngine software protected under
	copyright dated 2005 - 2008. Please see
	http://expressionengine.com/docs/license.html
----------------------------------------------------------------
	USE THIS SOFTWARE AT YOUR OWN RISK. WE ASSUME
	NO WARRANTY OR LIABILITY FOR THIS SOFTWARE AS DETAILED
	IN THE LICENSE AGREEMENT.
================================================================
	File:			pi.yearlist.php
----------------------------------------------------------------
	Version:		1.2
----------------------------------------------------------------
	Purpose:	  Returns list of years in which there are entries
----------------------------------------------------------------
	Compatibility:	EE 1.6.3
----------------------------------------------------------------
	Created:		2008-04-03
================================================================
*/

// -----------------------------------------
//	Information array
// -----------------------------------------

$plugin_info = array(
                 'pi_name'          => 'Year List',
                 'pi_version'       => '1.2',
                 'pi_author'        => 'Ryan Irelan',
                 'pi_author_url'    => 'http://ryanirelan.com',
                 'pi_description'   => 'Returns list of years in which there are entries',
                 'pi_usage'         => Yearlist::usage()
               );

// -----------------------------------------
//	Begin class
// -----------------------------------------

class Yearlist
{
    var $return_data;
    var $category;
	var $weblog;
	
    // -------------------------------
    // Constructor
    // -------------------------------
    
    function Yearlist ()
    {
		global $TMPL, $DB;
		
		// --------------------------
		// get the weblog parameter
		// --------------------------
		$weblog = $TMPL->fetch_param('weblog');
		if (!$TMPL->fetch_param('weblog'))
		{
			$error .= "You did not provide a weblog name, so this will not work!";
		} 	                                                                      
		// ---------------------------
		// get the category parameter
		// ---------------------------
		$category = ( ! $TMPL->fetch_param('category')) ? 'all' : $TMPL->fetch_param('category');                                             
		
		// ---------------------------
		// Query the database
		// ---------------------------
		$query = $DB->query("SELECT weblog_id FROM exp_weblogs WHERE blog_name = '".$DB->escape_str($weblog)."'");
		
		// ----------------------------
		// Is this a real weblog name?
		// ----------------------------
		if ($query->num_rows == 0)
		{
			$error = "The weblog name you provided does not exist. Please check your weblog name and try again.";
			return $error;
		}
		
		// ------------------------------
		// Build the query to get years
		// ------------------------------
		$weblog = $query->result[0]['weblog_id'];
		
		if ($category == 'all')
		{
			$query = $DB->query("SELECT DISTINCT year FROM exp_weblog_titles WHERE weblog_id = $weblog ORDER BY year DESC");			
		}
        else
		{
			// --------------------------------------------------
			// if the category is set to something besides all
			// we need to query for only entries that are in that
			// category
			// ---------------------------------------------------                               
			$query = $DB->query("SELECT DISTINCT exp_weblog_titles.year, exp_category_posts.entry_id FROM exp_weblog_titles INNER JOIN exp_category_posts ON exp_weblog_titles.entry_id = exp_category_posts.entry_id WHERE exp_weblog_titles.weblog_id = $weblog AND exp_category_posts.cat_id = $category ORDER BY year DESC");			
		}    
		
		// ----------------------------
		// Return query and parse tags
		// ----------------------------
		if ($query->num_rows == 0)
		{
			$this->return_data = "";
		}                           
		else
		{
			foreach ($query->result as $row)
			{
				$tagdata = $TMPL->tagdata;
				
				foreach ($TMPL->var_single as $key => $val)
				{
					if (isset($row[$val]))
					{
						$tagdata = $TMPL->swap_var_single($val, $row[$val], $tagdata);
					}
				}                                                                     
				$this->return_data .= $tagdata;
			}
		}
    }
    // END
	
	// -------------------------------
    // Usage
    // -------------------------------

	function usage()
	{
		ob_start(); 
?>
The Year Listing plugin is a simple way to get a distinct 4 digit year for your entries. This way you can list out years for archives.

{exp:yearlist weblog="yourWeblog" category="1"}

{year}

{/exp:yearlist}

That will return an array of years. Use {year} to print them to the screen and wrap in any markup needed. There are currently no linebreaks or HTML associated with this plugin.

The category parameter is optional and if you leave it out, the plugin will search across all categories.

<?php
		$buffer = ob_get_contents();
		ob_end_clean(); 

		return $buffer;
	}
	// END
	
}
// END CLASS
?>