<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domains\Communication\Chat\Infrastructure\Persistence\Models\ChatMessageModel;
use App\Domains\Communication\Chat\Infrastructure\Persistence\Models\ConversationModel;
use App\Domains\Communication\Chat\Presentation\Http\Resources\ChatMessageResource;
use App\Domains\Identity\User\Domain\Entities\User;
use App\Domains\Seller\Stores\Infrastructure\Persistence\Models\StoreModel;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

final class ChatStoreIdentityResourceTest extends TestCase
{
    public function test_seller_message_uses_store_identity(): void
    {
        $seller = new User(['name' => 'Nama Akun Seller']);
        $seller->setAttribute('id', '11111111-1111-4111-8111-111111111111');

        $store = new StoreModel(['name' => 'Ziip Official Store', 'logo' => 'stores/ziip.png']);
        $store->setAttribute('id', 10);
        $store->setAttribute('user_id', $seller->id);

        $conversation = new ConversationModel(['type' => 'store', 'store_id' => 10]);
        $conversation->setRelation('store', $store);

        $message = new ChatMessageModel([
            'conversation_id' => 20,
            'sender_id' => $seller->id,
            'message_type' => 'text',
            'message' => 'Pesan dari toko',
        ]);
        $message->setAttribute('id', 30);
        $message->setRelation('sender', $seller);
        $message->setRelation('conversation', $conversation);

        $data = (new ChatMessageResource($message))->toArray(Request::create('/'));

        $this->assertSame('Ziip Official Store', $data['sender_name']);
        $this->assertSame('stores/ziip.png', $data['sender_avatar']);
        $this->assertSame('store', $data['sender_identity_type']);
    }
}
