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
	public function enter(VirtualExecution $execution)
	{
		$execution->waitForSignal();
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function processSignal(VirtualExecution $execution, $signal, array $variables = [], array $delegation = [])
	{
		$this->passVariablesToExecution($execution, $variables);
	
		$this->leave($execution);
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
			$time,
			$execution,
			$activityId,
			($node === NULL) ? $execution->getNode() : $node
		));
	}
}
