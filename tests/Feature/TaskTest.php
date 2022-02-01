<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Model\Task;
use App\Model\User;

use function PHPUnit\Framework\assertJsonFileEqualsJsonFile;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function setUp():void
    {
        parent::setUp();

        $user=User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * @test
     */
    public function 一覧を取得できる()
    {
        $tasks=Task::factory()->count(10)->create();

        $response = $this->getJson('api/tasks');

        $response
            ->assertOk()
            ->assertJsonCount($tasks->count());
    }

    /**
     * @test
     */
    public function 登録することができる()
    {
        $data=[
            'title'=>'テスト投稿'
        ];

        $response = $this->postJson('api/tasks',$data);
        $response
            ->asserCreated()
            ->assertJsonFragment($data);
    }

    /**
     * @test
     */
    public function タイトルが空の場合は登録できない()
    {
        $data=[
            'title'=>''
        ];

        $response = $this->postJson('api/tasks',$data);
        $response
            ->asserStatus(422)
            ->assertJsonValidationError([
                'title'=>'タイトルは必ず指定して下さい。'
            ]);
    }

    /**
     * @test
     */
    public function タイトルが255文字の場合は登録できない()
    {
        $data=[
            'title'=>str_repeat('あ',256)
        ];

        $response = $this->postJson('api/tasks',$data);
        $response
            ->asserStatus(422)
            ->assertJsonValidationError([
                'title'=>'タイトルは255文字以下にして下さい。'
            ]);
    }

    /**
     * @test
     */
    public function 更新することができる()
    {
        $task=Task::factory()->create();

        $task->title='書き換え';

        $response = $this->patchJson("api/tasks/{$task->id}",$task->toArray());

        $response
            ->assertOK()
            ->assertJsonFragment($task->toArray());
    }


    /**
     * @test
     */
    public function 削除することができる()
    {
        $tasks=Task::factory()->count(10)->create();

        $tasks->title='書き換え';

        $response = $this->deleteJson('api/tasks/1');

        $response=$this->getJson('api/tasks');
        $response->assertJsonCount($tasks->count() -1);
    }
}
