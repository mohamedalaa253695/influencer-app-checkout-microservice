<?php
namespace App\Jobs;

use App\Models\Link;
use App\Models\LinkProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class LinkCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $data;
    private $link;
    private $linkProducts;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // echo 'Event:' . PHP_EOL;
        // echo print_r($this->data[0]) . PHP_EOL;
        // echo print_r($this->data[1]) . PHP_EOL;

        Link::create($this->data[0]);
        LinkProduct::insert($this->data[1]);
    }
}
