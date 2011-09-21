<?php
/**
 * Loads the regular expressions used to match SEF URL requests to their proper URLs.
 *
 * @copyright Copyright (C) 2008 FluxBB.org, based on code copyright (C) 2002-2008 PunBB.org
 * @license http://www.gnu.org/licenses/gpl.html GPL version 2 or higher
 * @package FluxBB
 */


$forum_rewrite_rules_pl = array(
	'/^watek[\/_-]?([0-9]+).*(nowe)[\/_-]?(posty?)(\.html?|\/)?$/i'															=>	'viewtopic.php?id=$1&action=new',
	'/^watek[\/_-]?([0-9]+).*(ostatni)[\/_-]?(post?)(\.html?|\/)?$/i'														=>	'viewtopic.php?id=$1&action=last',
	'/^post[\/_-]?([0-9]+)(\.html?|\/)?$/i'																					=>	'viewtopic.php?pid=$1',
	'/^(forum)?[\/_-]?([0-9]+).*[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'												=>	'viewforum.php?id=$2&p=$4',
	'/^(watek)[\/_-]?([0-9]+).*[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'												=>	'viewtopic.php?id=$2&p=$4',
	'/^kanal[\/_-]?(rss|atom)[\/_-]?forum[\/_-]?([0-9]+)[\/_-]?(\.xml?|\/)?$/i'												=>	'extern.php?action=feed&fid=$2&type=$1',
	'/^kanal[\/_-]?(rss|atom)[\/_-]?watek[\/_-]?([0-9]+)[\/_-]?(\.xml?|\/)?$/i'												=>	'extern.php?action=feed&tid=$2&type=$1',
	'/^(watek)[\/_-]?([0-9]+).*(\.html?|\/)?$/i'																			=>	'viewtopic.php?id=$2',
	'/^(forum)?[\/_-]?([0-9]+).*(\.html?|\/)?$/i'																			=>	'viewforum.php?id=$2',
	'/^nowa[\/_-]?odpowiedz[\/_-]?([0-9]+)(\.html?|\/)?$/i'																	=>	'post.php?tid=$1',
	'/^nowa[\/_-]?odpowiedz[\/_-]?([0-9]+)[\/_-]?cytuj[\/_-]?([0-9]+)(\.html?|\/)?$/i'										=>	'post.php?tid=$1&qid=$2',
	'/^nowy[\/_-]?watek[\/_-]?([0-9]+)(\.html?|\/)?$/i'																		=>	'post.php?fid=$1',
	'/^(usun)[\/_-]?([0-9]+)(\.html?|\/)?$/i'																				=>	'delete.php?id=$2',
	'/^(edytuj)[\/_-]?([0-9]+)(\.html?|\/)?$/i'																				=>	'edit.php?id=$2',
	'/^(loguj)(\.html?|\/)?$/i'																								=>	'login.php',
	'/^(szukaj)(\.html?|\/)?$/i'																							=>	'search.php',
	'/^(rejestruj)(\.html?|\/)?$/i'																							=>	'register.php',
	'/^(pomoc)(\.html?|\/)?$/i'																								=>	'help.php',
	'/^wyloguj[\/_-]?([0-9]+)[\/_-]([a-z0-9]+)(\.html?|\/)?$/i'																=>	'login.php?action=out&id=$1&csrf_token=$2',
	'/^nowe[\/_-]?haslo(\.html?|\/)?$/i'																					=>	'login.php?action=forget',
	'/^profil[\/_-]?([0-9]+)(\.html?|\/)?$/i'																				=>	'profile.php?id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?(glowne)(\.html?|\/)?$/i'																=>	'profile.php?section=essentials&id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?(personalne)(\.html?|\/)?$/i'															=>	'profile.php?section=personal&id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?(komunikatory)(\.html?|\/)?$/i'															=>	'profile.php?section=messaging&id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?(avatar-podpis)(\.html?|\/)?$/i'															=>	'profile.php?section=personality&id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?(wyswietlanie)(\.html?|\/)?$/i'															=>	'profile.php?section=display&id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?(prywatnosc)(\.html?|\/)?$/i'															=>	'profile.php?section=privacy&id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?(administracja)(\.html?|\/)?$/i'															=>	'profile.php?section=admin&id=$1',
	'/^profil[\/_-]?([0-9]+)[\/_-]?([a-z]+)(\.html?|\/)?$/i'																=>	'profile.php?section=$2&id=$1',
	'/^(usun)[\/_-]?(avatar)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'																=>	'profile.php?action=delete_$2&id=$3',
	'/^(wgraj)[\/_-]?(avatar)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'																=>	'profile.php?action=upload_$2&id=$3',
	'/^(zmien)[\/_-]?(email)[\/_-]?([0-9]+)[\/_-]([a-zA-Z0-9]+)(\.html?|\/)?$/i'											=>	'profile.php?action=change_email&id=$3&key=$4',
	'/^(zmien)[\/_-]?(email)[\/_-]?([0-9]+)(\.html?|\/)?$/i'																=>	'profile.php?action=change_email&id=$3',
	'/^(zmien)[\/_-]?(haslo)[\/_-]?([0-9]+)[\/_-]([a-zA-Z0-9]+)(\.html?|\/)?$/i'											=>	'profile.php?action=change_pass&id=$3&key=$4',
	'/^(zmien)[\/_-]?(haslo)[\/_-]?([0-9]+)(\.html?|\/)?$/i'																=>	'profile.php?action=change_pass&id=$3',
	'/^szukaj[\/_-]?(nowe)[\/_-]([0-9-]+)(\.html?|\/)?$/i'																	=>	'search.php?action=show_new&forum=$2',
	'/^szukaj[\/_-]?(nowe)[\/_-]([0-9-]+)[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'									=>	'search.php?action=show_new&forum=$2&p=$4',
	'/^szukaj[\/_-]?(ostatnie)[\/_-]([0-9]+)(\.html?|\/)?$/i'																=>	'search.php?action=show_recent&value=$2',
	'/^szukaj[\/_-]?(ostatnie)[\/_-]([0-9]+)[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'									=>	'search.php?action=show_recent&value=$2&p=$4',
	'/^szukaj[\/_-]?nowe(\.html?|\/)?$/i'																					=>	'search.php?action=show_new',
	'/^szukaj[\/_-]?nowe[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'														=>	'search.php?action=show_new&p=$3',
	'/^szukaj[\/_-]?ostatnie(\.html?|\/)?$/i'																				=>	'search.php?action=show_recent',
	'/^szukaj[\/_-]?ostatnie[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'													=>	'search.php?action=show_recent&p=$3',
	'/^szukaj[\/_-]?twoje-odpowiedzi(\.html?|\/)?$/i'																		=>	'search.php?action=show_replies',
	'/^szukaj[\/_-]?twoje-odpowiedzi[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'											=>	'search.php?action=show_replies&p=$3',
	'/^szukaj[\/_-]?bez-odpowiedzi(\.html?|\/)?$/i'																			=>	'search.php?action=show_unanswered',
	'/^szukaj[\/_-]?bez-odpowiedzi[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'											=>	'search.php?action=show_unanswered&p=$3',
	'/^szukaj[\/_-]?subskrybcje[\/_-]?([0-9]+)(\.html?|\/)?$/i'																=>	'search.php?action=show_subscriptions&user_id=$1',
	'/^szukaj[\/_-]?subskrybcje[\/_-]?([0-9]+)[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'								=>	'search.php?action=show_subscriptions&user_id=$1&p=$3',
	'/^szukaj[\/_-]?([0-9]+)(\.html?|\/)?$/i'																				=>	'search.php?search_id=$1',
	'/^szukaj[\/_-]?([0-9]+)[\/_-]?s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'												=>	'search.php?search_id=$1&p=$3',
	'/^szukaj[\/_-]?posty[\/_-]?uzytkownika[\/_-]?([0-9]+)(\.html?|\/)?$/i'													=>	'search.php?action=show_user_posts&user_id=$1',
	'/^szukaj[\/_-]?posty[\/_-]?uzytkownika[\/_-]?([0-9]+)[\/_-]?s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'					=>	'search.php?action=show_user_posts&user_id=$1&p=$3',
	'/^szukaj[\/_-]?watki[\/_-]?uzytkownika[\/_-]?([0-9]+)(\.html?|\/)?$/i'													=>	'search.php?action=show_user_topics&user_id=$1',
	'/^szukaj[\/_-]?watki[\/_-]?uzytkownika[\/_-]?([0-9]+)[\/_-]?s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'					=>	'search.php?action=show_user_topics&user_id=$1&p=$3',
	'/^uzytkownicy(\.html?|\/)?$/i'																							=>	'userlist.php',
	'/^uzytkownicy\/(.*)\/([0-9-]+)\/?([a-z_]+)[\/_-]([a-zA-Z]+)[\/_-]s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'				=>	'userlist.php?username=$1&show_group=$2&sort_by=$3&sort_dir=$4&p=$6', 
	'/^uzytkownicy\/(.*)\/([0-9-]+)\/?([a-z_]+)[\/_-]([a-zA-Z]+)(\.html?|\/)?$/i'											=>	'userlist.php?username=$1&show_group=$2&sort_by=$3&sort_dir=$4',
	'/^(email)[\/_-]?([0-9]+)(\.html?|\/)?$/i'																				=>	'misc.php?email=$2',
	'/^(raportuj)[\/_-]?([0-9]+)(\.html?|\/)?$/i'																			=>	'misc.php?report=$2',
	'/^(subskrybuj)[\/_-]?watek\/_-]?([0-9]+)(\.html?|\/)?$/i'																=>	'misc.php?action=subscribe&tid=$2',
	'/^(przerwij-subskrybcje)[\/_-]?watek\/_-]?([0-9]+)(\.html?|\/)?$/i'													=>	'misc.php?action=unsubscribe&tid=$2',
	'/^(subskrybuj)[\/_-]?([0-9]+)forum\/_-]?(\.html?|\/)?$/i'																=>	'misc.php?action=subscribe&fid=$2',
	'/^(przerwij-subskrybcje)[\/_-]?forum\/_-]?([0-9]+)(\.html?|\/)?$/i'													=>	'misc.php?action=unsubscribe&fid=$2',
	'/^(oznacz)[\/_-]?(przeczytane)?(\.html?|\/)?$/i'																		=>	'misc.php?action=markread',
	'/^(zasady)[\/_-]?(\.html?|\/)?$/i'																						=>	'misc.php?action=rules',
	'/^oznacz[\/_-](forum)[\/_-]?([0-9]+)[\/_-](przeczytane)(\.html?|\/)?$/i'												=>	'misc.php?action=markforumread&fid=$2',
	'/^moderuj[\/_-]?([0-9]+)(\.html?|\/)?$/i'																				=>	'moderate.php?fid=$1',
	'/^przenies[\/_-]?([0-9]+)[\/_-]([0-9]+)(\.html?|\/)?$/i'																=>	'moderate.php?fid=$1&move_topics=$2',
	'/^(otworz)[\/_-]?([0-9]+)[\/_-]([0-9]+)(\.html?|\/)?$/i'																=>	'moderate.php?fid=$2&open=$3',
	'/^(zamknij)[\/_-]?([0-9]+)[\/_-]([0-9]+)(\.html?|\/)?$/i'																=>	'moderate.php?fid=$2&close=$3',
	'/^(przyklej)[\/_-]?([0-9]+)[\/_-]([0-9]+)(\.html?|\/)?$/i'																=>	'moderate.php?fid=$2&stick=$3',
	'/^(odklej)[\/_-]?([0-9]+)[\/_-]([0-9]+)(\.html?|\/)?$/i'																=>	'moderate.php?fid=$2&unstick=$3',
	'/^moderuj[\/_-]?([0-9]+)[\/_-]?s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'												=>	'moderate.php?fid=$1&p=$3',
	'/^moderuj[\/_-]?([0-9]+)[\/_-]([0-9]+)(\.html?|\/)?$/i'																=>	'moderate.php?fid=$1&tid=$2',
	'/^moderuj[\/_-]?([0-9]+)[\/_-]([0-9]+)[\/_-]?s(trona)?[\/_-]?([0-9]+)(\.html?|\/)?$/i'									=>	'moderate.php?fid=$1&tid=$2&p=$4',
	'/^pokaz-host[\/_-]?([0-9]+|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(\.html?|\/)?$/i'							=>	'moderate.php?get_host=$1',
	'/^kanal[\/_-]?(rss|atom)(\.xml?|\/)?$/i'																				=>	'extern.php?action=feed&type=$1',
);

// Old english rules - for compatibility
require PUN_ROOT.'include/url/Default/rewrite_rules.php';
$forum_rewrite_rules = array_merge($forum_rewrite_rules_pl, $forum_rewrite_rules);