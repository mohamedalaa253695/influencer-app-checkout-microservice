<?php
namespace App\Jobs;

use App\Models\Link;
use App\Models\LinkProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LinkCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $link;
    private $linkProducts;

    public function __construct($link, $linkProducts)
    {
        $this->link = $link;
        $this->linkProducts = $linkProducts;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Link::create([
        //     'id' => $this->data['id'],
        //     'code' => $this->data['code'],
        //     'user_id' => $this->data['user_id'],
        //     'created_at' => $this->data['created_at'],
        //     'updated_at' => $this->data['updated_at'],
        // ]);

        Link::create($this->link);

        LinkProduct::insert($this->data['link_products']);
    }
}
