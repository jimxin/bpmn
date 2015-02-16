<?php

/*
 * This file is part of KoolKode BPMN.
*
* (c) Martin Schröder <m.schroeder2007@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace KoolKode\BPMN\Runtime\Behavior;

use KoolKode\BPMN\Engine\AbstractActivity;
use KoolKode\BPMN\Engine\VirtualExecution;
use KoolKode\BPMN\Runtime\Command\CreateTimerSubscriptionCommand;
use KoolKode\Expression\ExpressionInterface;
use KoolKode\Process\Node;

/**
 * @author Martin Schröder
 */
class IntermediateTimerDurationBehavior extends AbstractActivity implements IntermediateCatchEventInterface
{
	protected $duration;
	
	public function setDuration(ExpressionInterface $duration)
	{
		$this->duration = $duration;
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function enter(VirtualExecution $execution)
	{
		$execution->waitForSignal();
	}
	
	/**
	 * {@inheritdoc}
	 */
	protected function processSignal(VirtualExecution $execution, $signal = NULL, array $variables = [])
	{
		foreach($variables as $k => $v)
		{
			$execution->setVariable($k, $v);
		}
	
		$execution->takeAll();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function createEventSubscriptions(VirtualExecution $execution, $activityId, Node $node = NULL)
	{
		$interval = $this->getValue($this->duration, $execution->getExpressionContext());
		
		if(!$interval instanceof \DateInterval)
		{
			$interval = new \DateInterval((string)$interval);
		}
		
		$now = new \DateTimeImmutable();
		$time = $now->add($interval);
		
		$execution->getEngine()->executeCommand(new CreateTimerSubscriptionCommand(
			$execution,
			$time,
			$activityId,
			($node === NULL) ? $execution->getNode() : $node
		));
	}
}
