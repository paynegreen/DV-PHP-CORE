<?php

namespace Devless\RulesEngine;

trait date 
{
	/**
     * The `getTimestamp` method returns the current timestamp. eg: beforeQuerying()->getTimestamp()->storeAs($timestamp)->succeedWith($timestamp)
     * @return $this
     */
	public function getTimestamp()
	{
		if (!$this->execOrNot) {
                return $this;
        }
		$this->results = time();
        return $this;
	}

	/**
     * Get the current year using the `getCurrentYear` method eg:beforeQuering()->getCurrentYear()->storeAs($currentYear)succeedWith($currentYear)
     *
     * @return $this
     */
	public function getCurrentYear()
	{
		if (!$this->execOrNot) {
                return $this;
        }
        $this->results = date('Y');
        return $this;
	}

	/**
     *Get the current month using the `getCurrentMonth` method eg:beforeQuering()->getCurrentMonth()->storeAs($currentMonth)->succeedWith($currentMonth)
     *
     * @return $this
     */
	public function getCurrentMonth()
	{
		if (!$this->execOrNot) {
                return $this;
        }
        $this->results = date('M');
        return $this;
	}

	/**
     * Get the current day using the `getCurrentDay` method eg:beforeQuering()->getCurrentDay()->storeAs($currentDay)->succeedWith($currentDay)
     *
     * @return $this
     */
	public function getCurrentDay()
	{
		if (!$this->execOrNot) {
                return $this;
        }
        $this->results = date('D');
        return $this;
	}

	/**
     Get the current hour using the `getCurrentHour` method eg:beforeQuering()->getCurrentHour()->storeAs($currentHour)->succeedWith($currentHour)
     *
     * @return $this
     */
	public function getCurrentHour()
	{
		if (!$this->execOrNot) {
                return $this;
        }
        $this->results = date('h');
        return $this;
	}

	/**
     * Get the current minute using the `getCurrentMinute` method eg:beforeQuering()->getCurrentMinute()->storeAs($currentMinute)->succeedWith($currentMinute)
     *
     * @return $this
     */
	public function getCurrentMinute()
	{
		if (!$this->execOrNot) {
                return $this;
        }
        $this->results = date('i');
        return $this;
	}

	/**
    * Get the current second using the `getCurrentSecond` method eg:beforeQuering()->getCurrentSecond()->storeAs($currentSecond)->succeedWith($currentSecond)
     *
     * @return $this
     */
	public function getCurrentSecond()
	{
		if (!$this->execOrNot) {
                return $this;
        }
        $this->results = date('s');
        return $this;
	}

	/**
     * Get the current second using the `getFormattedDate` method eg:beforeQuering()->getFormattedDate()->storeAs($formattedDate)->succeedWith($formatedDate)
     *
     * @return $this
     */
	public function getFormattedDate()
	{
		if (!$this->execOrNot) {
                return $this;
        }
        $this->results = date('l jS \of F Y h:i:s A');
        return $this;
	}
}