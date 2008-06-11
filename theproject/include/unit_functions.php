<?php

require_once dirname(__FILE__).'/twitter.php';

class unit_functions {

	static function attack($transaction,$city,$db) {
      $ranged = 0;
      $melee = 0;
      $defense = $city->getValue('defense') + 1;
      foreach($city->getKeys() as $key) {
         $key2 = explode('_',$key);
         if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;

         $uranged = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='ranged_hit'") or die(mysql_error());
         $uranged = mysql_fetch_assoc($uranged);
         $ranged += $uranged['value']*$city->getValue($key);

         $umelee = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='melee_hit'") or die(mysql_error());
         $umelee = mysql_fetch_assoc($umelee);
         $melee += $umelee['value']*$city->getValue($key);

         $udefense = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='defense'") or die(mysql_error());
         $udefense = mysql_fetch_assoc($udefense);
         $defense += $udefense['value']*$city->getValue($key);

      }//end foreach keys

      $random_bound = ($ranged + $melee + $defense)/rand(2,6);
      $randomness = rand($random_bound*-1,$random_bound);

      $uranged = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='ranged_hit'") or die(mysql_error());
      $uranged = mysql_fetch_assoc($uranged);
      $uranged = $uranged['value']*$transaction['unit_count'];

      $umelee = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='melee_hit'") or die(mysql_error());
      $umelee = mysql_fetch_assoc($umelee);
      $umelee = $umelee['value']*$transaction['unit_count'];

      $udefense = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='defense'") or die(mysql_error());
      $udefense = mysql_fetch_assoc($udefense);
      $udefense = $udefense['value']*$transaction['unit_count'];

      $ranged_attack = $ranged + $randomness - $uranged;
      $melee_attack = $melee + $randomness - $umelee;
      $total_defense = $defense + $randomness - $udefense;
      $attack_results = ($ranged_attack + $melee_attack) + $defense;
		$lower_loss_bound = ($ranged+$melee) < $transaction['unit_count']/2 ? $ranged+$melee : $transaction['unit_count']/4;

		if($attack_results < 0) {//we only have to do something if the attacker made an impact
         foreach($city->getKeys() as $key) {
            $key2 = explode('_',$key);
            if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;
            $attack_results += $city->getValue($key);
            if($attack_results > 0) {//we have destroyed all the units we are going to
               $city->setValue($key, $attack_results);
               break;
            }//end if attack_results > 0
            $city->setValue($key, 0);
         }//end foreach keys
      }//end if attack_results < 0

      $user = new user($transaction['user_id'],new server($transaction['server_id']));
      if($attack_results < 0) {//after destroying units the attacker still prevails, take city
         $user->setValue('city_count', intval($user->getValue('city_count'))+1);
         $city->getValue('user')->setValue('city_count', intval($city->getValue('user')->getValue('city_count'))-1);
         mysql_query("UPDATE server_cities SET user_id=".$transaction['user_id']." WHERE city_id=".$transaction['destination'],$db) or die(mysql_error());
         $lower_loss_bound = ($ranged+$melee) < $transaction['unit_count']/2 ? $ranged+$melee : $transaction['unit_count']/4;
         $city->setValue('unit_'.$transaction['unit_id'], $transaction['unit_count']-rand($lower_loss_bound,$transaction['unit_count']/2));
      }//end if attack_results < 0

      mysql_query("INSERT INTO server_attack_results (server_id,destination,user_id,results,time) VALUES (".$transaction['server_id'].",".$transaction['destination'].",".$transaction['user_id'].",$attack_results,".time().")",$db) or die(mysql_error());

		$class = ($attack_results < 0) ? 'won' : 'lost';
		$city_link = '';
		if($class == 'won') $city_link .= '<a href="/server/'.$transaction['server_id'].'/city/'.$city->getValue('id').'">';
		$city_link .= $city->getValue('name') ? mysql_real_escape_string($city->getValue('name'),$db) : 'City at '.$city->getValue('id');
		if($class == 'won') $city_link .= '</a>';
      mysql_query("INSERT INTO messages (server_id,user_id,time,message) VALUES (".$transaction['server_id'].",".$transaction['user_id'].",".time().",'<span class=\"$class\">Attack against $city_link $class</span>')",$db) or die(mysql_error());
		if($user->getValue('twitter'))
			send_tweet($user->getValue('twitter'), strip_tags("Attack against $city_link $class"));

		$other_class = ($class == 'won') ? 'lost' : 'won';
		$city_link = '';
		if($class == 'lost') $city_link .= '<a href="/server/'.$transaction['server_id'].'/city/'.$city->getValue('id').'">';
		$city_link .= $city->getValue('name') ? mysql_real_escape_string($city->getValue('name'),$db) : 'City at '.$city->getValue('id');
		if($class == 'lost') $city_link .= '</a>';
		$user_link .= '<a href="/server/'.$transaction['server_id'].'/user/'.$user->getValue('userid').'">'.($user->getValue('nickname') ? mysql_real_escape_string($user->getValue('nickname'),$db) : 'User #'.$user->getValue('userid')).'</a>';
      mysql_query("INSERT INTO messages (server_id,user_id,time,message) VALUES (".$transaction['server_id'].",".$city->getValue('user')->getValue('userid').",".time().",'<span class=\"$other_class\">$user_link attacked you at $city_link and $class</span>')",$db) or die(mysql_error());
		if($city->getValue('user')->getValue('twitter'))
			send_tweet($city->getValue('user')->getValue('twitter'), strip_tags("$user_link attacked you at $city_link and $class"));

	}//end function attack

	static function spy($transaction,$city,$db) {
      $defense = $city->getValue('defense') + 1;
      foreach($city->getKeys() as $key) {
         $key2 = explode('_',$key);
         if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;
         $udefense = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='defense'") or die(mysql_error());
         $udefense = mysql_fetch_assoc($udefense);
         $defense += $udefense['value']*$city->getValue($key);
      }//end foreach keys

      $random_bound = ($defense)/rand(2,6);
      $randomness = rand($random_bound*-1,$random_bound);

      $skill = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='skill'") or die(mysql_error());
      $skill = mysql_fetch_assoc($skill);
      $skill = $skill['value']*$transaction['unit_count'];

		$results = $defense + $randomness - $skill;

      $user = new user($transaction['user_id'],new server($transaction['server_id']));
		if($results < 0) {//only do something if successful
			$eta = time() + ($results*-1)*40;
			$city->setValue('user_'.$transaction['user_id'].'_access',$eta);
		}//end if results < 0

		$class = ($results < 0) ? 'succeeded' : 'failed';
		$city_link = '';
		if($class == 'succeeded') $city_link .= '<a href="/server/'.$transaction['server_id'].'/city/'.$city->getValue('id').'">';
		$city_link .= $city->getValue('name') ? mysql_real_escape_string($city->getValue('name'),$db) : 'City at '.$city->getValue('id');
		if($class == 'succeeded') $city_link .= '</a>';
      mysql_query("INSERT INTO messages (server_id,user_id,time,message) VALUES (".$transaction['server_id'].",".$transaction['user_id'].",".time().",'<span class=\"$class\">Spies infiltrating $city_link $class</span>')",$db) or die(mysql_error());
		if($user->getValue('twitter'))
			send_tweet($user->getValue('twitter'), strip_tags("Spies infiltrating $city_link $class"));

	}//end function spy

	static function loot($transaction,$city,$db) {
      $defense = $city->getValue('defense') + 1;
      foreach($city->getKeys() as $key) {
         $key2 = explode('_',$key);
         if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;
         $udefense = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='defense'") or die(mysql_error());
         $udefense = mysql_fetch_assoc($udefense);
         $defense += $udefense['value']*$city->getValue($key);
      }//end foreach keys

      $random_bound = ($defense)/rand(2,6);
      $randomness = rand($random_bound*-1,$random_bound);

      $skill = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='skill'") or die(mysql_error());
      $skill = mysql_fetch_assoc($skill);
      $skill = $skill['value']*$transaction['unit_count'];

		$results = $defense + $randomness - $skill;

		$gold = 0;
		$user = new user($transaction['user_id'],new server($transaction['server_id']));
		if($results < 0) {//only do something if successful
			$has_gold = intval($city->getValue('user')->getValue('gold'));
			$gold = ($results*-1)*10;
			$gold = $gold < $has_gold ? $gold : $has_gold;
			$city->getValue('user')->setValue('gold',$has_gold-$gold);
			$user->setValue('gold',intval($user->getValue('gold'))+$gold);
		}//end if results < 0

		$class = ($results < 0) ? 'succeeded' : 'failed';
		$city_link .= $city->getValue('name') ? mysql_real_escape_string($city->getValue('name'),$db) : 'City at '.$city->getValue('id');
		mysql_query("INSERT INTO messages (server_id,user_id,time,message) VALUES (".$transaction['server_id'].",".$transaction['user_id'].",".time().",'<span class=\"$class\">Looting $city_link $class ($gold gold gained)</span>')",$db) or die(mysql_error());
		if($user->getValue('twitter'))
			send_tweet($user->getValue('twitter'), strip_tags("Looting $city_link $class ($gold gold gained)"));

		$other_class = $class=='succeeded' ? 'failed' : 'succeeded';
		$user_link .= '<a href="/server/'.$transaction['server_id'].'/user/'.$user->getValue('userid').'">'.($user->getValue('nickname') ? mysql_real_escape_string($user->getValue('nickname'),$db) : 'User #'.$user->getValue('userid')).'</a>';
		mysql_query("INSERT INTO messages (server_id,user_id,time,message) VALUES (".$transaction['server_id'].",".$city->getValue('user')->getValue('userid').",".time().",'<span class=\"$other_class\">$user_link tried looting $city_link and $class ($gold gold lost)</span>')",$db) or die(mysql_error());
		if($city->getValue('user')->getValue('twitter'))
			send_tweet($city->getValue('user')->getValue('twitter'), strip_tags("$user_link tried looting $city_link and $class ($gold gold lost)"));

	}//end function loot

	static function subvert($transaction,$city,$db) {
      $defense = $city->getValue('defense') + 1;
      foreach($city->getKeys() as $key) {
         $key2 = explode('_',$key);
         if($key2[0] != 'unit' || !is_numeric($key2[1])) continue;
         $udefense = mysql_query("SELECT value FROM units_data WHERE unit_id=".$key2[1]." AND `key`='defense'") or die(mysql_error());
         $udefense = mysql_fetch_assoc($udefense);
         $defense += $udefense['value']*$city->getValue($key);
      }//end foreach keys

      $random_bound = ($defense)/rand(2,6);
      $randomness = rand($random_bound*-1,$random_bound);

      $skill = mysql_query("SELECT value FROM units_data WHERE unit_id=".$transaction['unit_id']." AND `key`='skill'") or die(mysql_error());
      $skill = mysql_fetch_assoc($skill);
      $skill = $skill['value']*$transaction['unit_count'];

		$results = $defense + $randomness - $skill;

		$pop = 0;
		$user = new user($transaction['user_id'],new server($transaction['server_id']));
		if($results < 0) {//only do something if successful
			$has_pop = intval($city->getValue('population'));
			$pop = ($results*-1)*4;
			$pop = $pop < $has_pop ? $pop : $has_pop;
			$city->setValue('population',$has_pop-$pop);
			$cities = $user->getValue('cities');
			$cities[0]->setValue('population',$cities[0]->getValue('population')+$pop);
		}//end if results < 0

		$class = ($results < 0) ? 'succeeded' : 'failed';
		$city_link .= $city->getValue('name') ? mysql_real_escape_string($city->getValue('name'),$db) : 'City at '.$city->getValue('id');
		mysql_query("INSERT INTO messages (server_id,user_id,time,message) VALUES (".$transaction['server_id'].",".$transaction['user_id'].",".time().",'<span class=\"$class\">Subverting $city_link $class ($pop people turned)</span>')",$db) or die(mysql_error());
		if($user->getValue('twitter'))
			send_tweet($user->getValue('twitter'), strip_tags("Subverting $city_link $class ($pop people turned)"));

		$other_class = $class=='succeeded' ? 'failed' : 'succeeded';
		$user_link .= '<a href="/server/'.$transaction['server_id'].'/user/'.$user->getValue('userid').'">'.($user->getValue('nickname') ? mysql_real_escape_string($user->getValue('nickname'),$db) : 'User #'.$user->getValue('userid')).'</a>';
		mysql_query("INSERT INTO messages (server_id,user_id,time,message) VALUES (".$transaction['server_id'].",".$city->getValue('user')->getValue('userid').",".time().",'<span class=\"$other_class\">$user_link tried subverting $city_link and $class ($pop people turned)</span>')",$db) or die(mysql_error());
		if($city->getValue('user')->getValue('twitter'))
			send_tweet($city->getValue('user')->getValue('twitter'), strip_tags("$user_link tried subverting $city_link and $class ($pop people turned)"));

	}//end function subvert


}//end class

?>
