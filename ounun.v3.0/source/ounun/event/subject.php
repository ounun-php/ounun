<?php

namespace ounun\event;

class subject implements \SplSubject
{
    /** @var \SplObjectStorage */
    protected $observers;

    /** @var string 当前事件 */
    public $event;

    /**
     * subject constructor.
     */
    public function __construct()
    {
        $this->observers = new \SplObjectStorage();
    }

    /**
     * @param \SplObserver $observer
     */
    public function attach(\SplObserver $observer)
    {
        $this->observers->attach($observer);
    }

    /**
     * @param \SplObserver $observer
     */
    public function detach(\SplObserver $observer)
    {
        $this->observers->detach($observer);
//        if($index = array_search($observer, $this->observers, true)){
//            unset($this->observers[$index]);
//        }
    }

    /**
     * @param string $event
     */
    public function notify($event = '')
    {
        if ($event) {
            $this->event = $event;
        }
        $this->observers->rewind();
        while ($this->observers->valid()) {
            $this->observers->current()->update($this);
            $this->observers->next();
        }
//        foreach ($this->observers as $observer){
//            $observer->update($this);
//        }
    }
}
