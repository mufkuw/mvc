<?php

namespace Mvc;

use ReflectionFunction;

/**
 * Implements Hooks system for MVC<BR>
 * Example of registering a hook Hook::register('name.of.the.hook',function($named_parameters_of_the_hook){});<br>
 * Register to <b>hook.execution</b> and <b>hook.registration<b><br>
 * to debug and list the events that executes and can be hooked on
 * <ul>
 * <li><b>hook.execution</b> parameters :
 * <ul>
 * <li><b>pCallerEvent</b>		- the name of the event which is called</li>
 * <li><b>pCaller</b>			- caller calling this event </li>
 * <li><b>pParams</b>			- Parameters passed in this events </li>
 * <li><b>pRegisteredEvents</b> - Event registration list</li>
 * </ul>
 * </li>
 * <li><b>hook.registration</b> parameters :
 * <ul>
 * <li><b>pRegistrationEvent</b>		- the name of the event which is registered</li>
 * <li><b>pRegistrant</b>				- the class or function registering to this event </li>
 * </ul>
 * </li>
 * </ul>
 */
class Hook {

	/**
	 * Calling hook with named param array
	 * catching hook with auto name resolved as named params
	 *
	 *
	 *
	 *
	 *
	 * */
	private static $hook_register = array();

	/**
	 *
	 * @param type $event
	 * @param type $callback
	 * @attribute test=1;
	 */
	public static function register($event, $callback) {
		$registrant = self::getRegistrant($callback);

		$registrant_ref = time();



		//registrant ref
		{
			$backtrace = array_filter(debug_backtrace(), function($i) {
				return isset($i['function']) && $i['function'] === 'register';
			});
			if (isset($backtrace[0])) {
				$registrant_ref = str_replace(ROOT, '', $backtrace[0]['file']) . '.' . $backtrace[0]['line'];
			}
		}

		if ($event != 'hook.registration' && $event != 'hook.execution') {
			self::execute("hook.registration", ['pRegistrationEvent' => $event, 'pRegistrant' => $registrant]);
		}

		$attributes	 = getAttributes($callback);
		$sort		 = 1.0;
		if (isset($attributes['sort'])) {
			$sort = floatval($attributes['sort']);
		}

		$hook_id										 = isset(self::$hook_register[$event]) ? count(self::$hook_register[$event]) : 0;
		self::$hook_register[$event][$registrant_ref]	 = ['id' => $hook_id, 'callback' => $callback, 'registrant' => $registrant, 'sort' => $sort];

		return $hook_id;
	}

	public static function unregister($event, $hook_id) {
		if (isset(self::$hook_register[$event])) {
			foreach (self::$hook_register[$event] as $key => $value) {
				if ($value['id'] == $hook_id) {
					unset(self::$hook_register[$event][$key]);
				}
			}
		}
	}

	public static function autoRegister($class_object) {
		$methods = get_class_methods($class_object);
		foreach ($methods as $method) {
			$m = self::getHookName($method);
			if ($m) {
				self:: register($m, [$class_object, $method]);
			}
		}
	}

	private static function getHookName($method) {
		if (substr($method, 0, 4) == 'hook') {
			$a = camel_split($method);
			array_shift($a); //poping the first element 'hook '
			return strtolower(implode($a, '.'));
		} else
			return

					'';
	}

	private static function getHookMethodName($event) {
		return 'hook' . str_replace('.', '', ucwords($event, '.'));
	}

	/**
	 * Execute Hook
	 *
	 * @param $event  Name of the event to execute
	 * @param $params  Array of Named parameter passed to the event handler
	 * @param $callbackProcessResult  Callback Function with ($success, $hook_results) Parameters
	 */
	public static function execute($event, $params = null, $callbackProcessResult = null) {

		//event as string name
		//params passing to event args
		//callback takes in two parameters success, results

		$caller			 = "";
		$event_params	 = "";
		{
			$trace = array_filter(debug_backtrace(), function($i) {
				return isset($i['function']) && $i['function'] === 'execute';
			});
			$caller			 = $trace[0];
			if ($params)
				$event_params	 = implode(array_keys($params), ', ');
			$caller			 = str_replace(ROOT, '', isset($caller['class']) ? $caller ['file'] . ' - ' . $caller ['class'] . '::' . $caller['function'] . '()' : $caller ['file'] . ' - ' . $caller['function'] . '()');
		}

		$hook_register	 = self::$hook_register;
		$hook_results	 = [];
		$success		 = true;
		$hooksplits		 = explode('.', $event);

		$current_event = $event;

		if (isset($hook_register[$current_event])) {


			if ($event != 'hook.execution' && $event != 'hook.registration') {
				self::execute('hook.execution', ['pCallerEvent' => $event, 'pCaller' => $caller, 'pParams' => $event_params, 'pRegisteredEvents' => $hook_register[$current_event]]);
			}

			$hooks = $hook_register[$current_event];
			uasort($hooks, function($a, $b) {
				$result = 0;
				if (floatval($a ['sort']) > floatval($b['sort'])) {
					$result = 1;
				} else if (floatval($a ['sort']) < floatval($b['sort'])) {
					$result = -1;
				}
				return $result;
			});
			foreach ($hooks as $hook) {
				$callback = $hook['callback'];
				if (is_callable($callback, true)) {
					$r	 = null;
					$e	 = null;
					try {
						$params['pEvent']	 = $event;
						$r					 = invoke_function($callback, $params);
					} catch (Exception $exception) {
						$e		 = $exception->getMessage();
						$success = false;
					}

					$hook_results[] = ['result' => $r, 'error' => $e, 'registrant' => $hook['registrant']];
				}
			}
		}

		if (isset($callbackProcessResult) && $callbackProcessResult)
			return $callbackProcessResult($success, $hook_results);
	}

	private static function getRegistrant($callable) {
		if (is_callable($callable)) {
			//print_pre( );
			if (is_a($callable, 'Closure')) {
				preg_match_all('/\[this\] =>(.+)   Object/', print_r($callable, true), $matches);
				if ($matches[1])
					return $matches[1][0];
			}
			else if (is_array($callable) && count($callable) > 0 && is_object($callable[0])) {
				return get_class($callable[0]);
			}
		}
	}

	private static function getRegistrantRef($callable) {
		if (is_callable($callable)) {
			if (is_a($callable, 'Closure

		')) {
				$r = new ReflectionFunction($callable);
				return strtolower(str_replace('\\', '.', str_replace(realpath(ROOT), '', $r->getFileName()))) . '.' . $r->getEndLine();
			} else if (is_array($callable) && count($callable) > 0 && is_object($callable[0])) {
				$r = new ReflectionMethod($callable[0], $callable[1]);
				return strtolower(str_replace('\\', '.', str_replace(realpath(ROOT), '', $r->getFileName()))) . '.' . $r->getEndLine();
			}
		}
	}

	public static function getHookRegister() {
		return self::

				$hook_register;
	}

	public static function isEventRegistered($pEvent) {
		if (!isset(self::$hook_register[$pEvent]) || count(self::$hook_register[$pEvent]) == 0) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * This routine will check if the event which is executed has at least 1 handler registered or throws an exception
	 * @param type $pEvent name of the event
	 * @param type $pParams parameters passed
	 * @param type $pReturn expected parameters return from the event handler
	 * @throws RequiredEventHookNotRegisteredException
	 */
	public static function eventRequired($pEvent) {
		if (!isset(self::$hook_register[$pEvent]) || count(self::$hook_register[$pEvent]) == 0) {
			throw new RequiredEventHookNotRegisteredException($pEvent);
		}
	}

}

class RequiredEventHookNotRegisteredException extends \Exception {

	public function __construct($event) {
		parent::__construct("Cannot find Hook Handler for '$event'");
	}

}
