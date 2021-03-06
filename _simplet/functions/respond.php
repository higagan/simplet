<?php

////	Respond
//
// Process a posted response.
//
// Respond();
// Respond(true);

function Respond($Status_Override = false) {

	global $Database, $Forum_Reply_Default, $Forum_Reply_Inherit, $Member, $Sitewide, $Time;

	// Prepare an array to be returned as JSON.
	$Response_Return = array();
	$Response_Return['error'] = array();

	// IFEXISTSRESPOND
	if (
		!$Database['Exists']['Topics'] ||
		!$Database['Exists']['Responses']
	) {
		array_push($Response_Return['error'], 'This site has not been correctly configured to have responses.');

	// IFPOSTSET
	} else if (
			!isset($_POST['canonical']) ||
			!isset($_POST['type']) ||
			!isset($_POST['post']) ||
			!isset($_POST['csrf_protection'])
	) {
		// Catch errors
		if ( !isset($_POST['canonical']) ) {
			array_push($Response_Return['error'], 'Could not determine which post you wanted to leave a response to.');
		}
		if ( !isset($_POST['type']) ) {
			array_push($Response_Return['error'], 'Response type was not set correctly.');
		}
		if ( !isset($_POST['post']) ) {
			array_push($Response_Return['error'], 'You didn\'t enter a post.');
		}

	// IFCSRFPROTECTED
	} else if ( !Runonce_CSRF_Check($_POST['csrf_protection']) ) {
		array_push($Response_Return['error'], 'CSRF Protection failed. Please try again.');
	} else {

		// Set Variables
		$Response_Canonical = Input_Prepare($_POST['canonical']);
		$Response_Type = Input_Prepare($_POST['type']);
		$Response_Post = trim(htmlentities($_POST['post'], ENT_QUOTES, 'UTF-8'));
		$Response_Prepared = trim(Input_Prepare($_POST['post']));

		// TODO Redirect responsive
		if ( !$Member['Auth'] ) {
			return array('error' => array('Sorry, you aren\'t logged in anymore. You must <a href="'.$Sitewide['Root'].$Sitewide['Account'].'?login&redirect='.urlencode($Sitewide['Root'].$Response_Canonical).'">Log In</a> to '.$Response_Type.'.'));
		}

		// Response Rating
		if ($Response_Type == 'Review') {
			if ( isset($_POST['rating']) ) {
				$Response_Rating = strval(Input_Prepare($_POST['rating']));
			} else {
				array_push($Response_Return['error'], 'You didn\'t choose a rating.');
			}
		} else {
			$Response_Rating = 0;
		}

		// Response Status
		if ( isset($Status_Override) && $Status_Override ) {
			$Response_Status = $Status_Override;
		} else if ($Response_Type == 'Post') {
			if ( $Forum_Reply_Inherit === true ) {

				// Fetch Status of Topic
				$Topic_Status_Query = 'SELECT `Status` FROM `'.$Database['Prefix'].'Topics` WHERE `Slug`=\''.$Response_Canonical.'\' AND (`Status`=\'Public\' OR `Status`=\'Private\')';
				$Topic_Status_Query = mysqli_query($Database['Connection'], $Topic_Status_Query, MYSQLI_STORE_RESULT);
				if ( !$Topic_Status_Query ) {
					array_push($Response_Return['error'], 'Topic Status Query Error.');
				}
				$Topic_Status_Count = mysqli_num_rows($Topic_Status_Query);
				if ( $Topic_Status_Count === 0 ) {
					array_push($Response_Return['error'], 'Topic Status Check Error. Using Fallback.');
					$Response_Status = $Forum_Reply_Default;
				} else {
					$Topic_Status_Fetch = mysqli_fetch_assoc($Topic_Status_Query);
					$Response_Status = $Topic_Status_Fetch['Status'];
				}

			} else {
				$Response_Status = $Forum_Reply_Default;
			}

		} else if ($Response_Type == 'Reviews') {
			$Response_Status = $Response_Status_Reviews;
		} else if ($Response_Type == 'Comments') {
			$Response_Status = $Response_Status_Comments;
		} else {
			$Response_Status = 'Public';
		}

		// Query
		$Response_Query = 'INSERT INTO `'.$Database['Prefix'].'Responses` (`Member_ID`, `Canonical`, `Type`, `Status`, `Helpfulness`, `Rating`, `Post`, `Created`, `Modified`) VALUES (\''.$Member['ID'].'\', \''.$Response_Canonical.'\', \''.$Response_Type.'\', \''.$Response_Status.'\', \'0\', \''.$Response_Rating.'\', \''.$Response_Prepared.'\', \''.$Time['Now'].'\', \''.$Time['Now'].'\')';
		$Response_New = mysqli_query($Database['Connection'], $Response_Query, MYSQLI_STORE_RESULT);
		if ( !$Response_New ) {
			array_push($Response_Return['error'], 'Invalid Query (Review_New): '.mysqli_error($Database['Connection']));
		}

		// Prepare statements to be returned.
		$Response_ID = mysqli_insert_id($Database['Connection']);
		$Response_Parsed = Output_Parse($Response_Post);
		$Response_Return['id'] = $Response_ID;
		$Response_Return['post'] = $Response_Parsed;
		$Response_Return['rating'] = $Response_Rating;

		// If the response is (Public or Private) and is a Forum Post
		if (
			(
				$Response_Status === 'Public' ||
				$Response_Status === 'Private'
			) &&
			$Response_Type == 'Post'
		) {
			// Update Responses Count for Topic
			// Also updates Modified Time for Topic
			Forum_Topic_Increment($Response_Canonical);
			// Get the Category Name from Topic
			$Topic_Info = Forum_Topic_Info($Response_Canonical);
			// Use the Category Name to update the Modified Time
			Forum_Category_Modified($Topic_Info['Category']);
		}

	}

	return $Response_Return;
}