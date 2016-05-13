<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class FriendshipsTest extends \Arubacao\Tests\Friendships\AbstractTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function user_can_send_a_friend_request()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();

        $sender->befriend($recipient);

        $this->assertCount(1, $recipient->getFriendRequests());
    }

    /** @test */
    public function user_can_not_send_a_friend_request_if_frienship_is_pending()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $sender->befriend($recipient);
        $sender->befriend($recipient);
        $sender->befriend($recipient);

        $this->assertCount(1, $recipient->getFriendRequests());
    }


    /** @test */
    public function user_can_send_a_friend_request_if_frienship_is_denied()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();

        $sender->befriend($recipient);
        $recipient->denyFriendRequest($sender);

        $sender->befriend($recipient);

        $this->assertCount(1, $recipient->getFriendRequests());
    }

    /** @test */
    public function user_is_friend_with_another_user_if_accepts_a_friend_request()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        //send fr
        $sender->befriend($recipient);
        //accept fr
        $recipient->acceptFriendRequest($sender);

        $this->assertTrue($recipient->isFriendWith($sender));
        $this->assertTrue($sender->isFriendWith($recipient));
        //fr has been delete
        $this->assertCount(0, $recipient->getFriendRequests());
    }

    /** @test */
    public function user_is_not_friend_with_another_user_until_he_accepts_a_friend_request()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        //send fr
        $sender->befriend($recipient);

        $this->assertFalse($recipient->isFriendWith($sender));
        $this->assertFalse($sender->isFriendWith($recipient));
    }

    /** @test */
    public function user_has_friend_request_from_another_user_if_he_received_a_friend_request()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        //send fr
        $sender->befriend($recipient);

        $this->assertTrue($recipient->hasFriendRequestFrom($sender));
        $this->assertFalse($sender->hasFriendRequestFrom($recipient));
    }

    /** @test */
    public function user_has_not_friend_request_from_another_user_if_he_accepted_the_friend_request()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        //send fr
        $sender->befriend($recipient);
        //accept fr
        $recipient->acceptFriendRequest($sender);

        $this->assertFalse($recipient->hasFriendRequestFrom($sender));
        $this->assertFalse($sender->hasFriendRequestFrom($recipient));
    }

    /** @test */
    public function user_cannot_accept_his_own_friend_request(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();

        //send fr
        $sender->befriend($recipient);

        $sender->acceptFriendRequest($recipient);
        $this->assertFalse($recipient->isFriendWith($sender));
    }

    /** @test */
    public function user_can_deny_a_friend_request()
    {
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $sender->befriend($recipient);

        $recipient->denyFriendRequest($sender);

        $this->assertFalse($recipient->isFriendWith($sender));

        //fr has been delete
        $this->assertCount(0, $recipient->getFriendRequests());
        $this->assertCount(1, $sender->getDeniedFriendships());
    }

    /** @test */
    public function user_can_block_another_user(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();

        $sender->blockFriend($recipient);

        $this->assertTrue($recipient->isBlockedBy($sender));
        $this->assertTrue($sender->hasBlocked($recipient));
        //sender is not blocked by receipient
        $this->assertFalse($sender->isBlockedBy($recipient));
        $this->assertFalse($recipient->hasBlocked($sender));
    }

    /** @test */
    public function user_can_unblock_a_blocked_user(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipient = factory(Arubacao\Tests\Friendships\Models\User::class)->create();

        $sender->blockFriend($recipient);
        $sender->unblockFriend($recipient);

        $this->assertFalse($recipient->isBlockedBy($sender));
        $this->assertFalse($sender->hasBlocked($recipient));
    }

    /** @test */
    public function it_returns_all_user_friendships(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(3)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(3, $sender->getAllFriendships());
    }

    /** @test */
    public function it_returns_accepted_user_friendships_number(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(3)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertEquals(2, $sender->getFriendsCount());
    }

    /** @test */
    public function it_returns_accepted_user_friendships(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(3)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(2, $sender->getAcceptedFriendships());
    }

    /** @test */
    public function it_returns_only_accepted_user_friendships(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(4)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(2, $sender->getAcceptedFriendships());

        $this->assertCount(1, $recipients[0]->getAcceptedFriendships());
        $this->assertCount(1, $recipients[1]->getAcceptedFriendships());
        $this->assertCount(0, $recipients[2]->getAcceptedFriendships());
        $this->assertCount(0, $recipients[3]->getAcceptedFriendships());
    }

    /** @test */
    public function it_returns_pending_user_friendships(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(3)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $this->assertCount(2, $sender->getPendingFriendships());
    }

    /** @test */
    public function it_returns_denied_user_friendships(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(3)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $this->assertCount(1, $sender->getDeniedFriendships());
    }

    /** @test */
    public function it_returns_blocked_user_friendships(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(3)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->blockFriend($sender);
        $this->assertCount(1, $sender->getBlockedFriendships());
    }

    /** @test */
    public function it_returns_user_friends(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(4)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);

        $this->assertCount(2, $sender->getFriends());
        $this->assertCount(1, $recipients[1]->getFriends());
        $this->assertCount(0, $recipients[2]->getFriends());
        $this->assertCount(0, $recipients[3]->getFriends());

        $this->containsOnlyInstancesOf(Arubacao\Tests\Friendships\Models\User::class, $sender->getFriends());
    }

    /** @test */
    public function it_returns_user_friends_per_page(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(6)->create();

        foreach ($recipients as $recipient) {
            $sender->befriend($recipient);
        }

        $recipients[0]->acceptFriendRequest($sender);
        $recipients[1]->acceptFriendRequest($sender);
        $recipients[2]->denyFriendRequest($sender);
        $recipients[3]->acceptFriendRequest($sender);
        $recipients[4]->acceptFriendRequest($sender);


        $this->assertCount(2, $sender->getFriends(2));
        $this->assertCount(4, $sender->getFriends(0));
        $this->assertCount(4, $sender->getFriends(10));
        $this->assertCount(1, $recipients[1]->getFriends());
        $this->assertCount(0, $recipients[2]->getFriends());
        $this->assertCount(0, $recipients[5]->getFriends(2));

        $this->containsOnlyInstancesOf(Arubacao\Tests\Friendships\Models\User::class, $sender->getFriends());
    }

    /** @test */
    public function it_returns_user_friends_of_friends(){
        $sender = factory(Arubacao\Tests\Friendships\Models\User::class)->create();
        $recipients = factory(Arubacao\Tests\Friendships\Models\User::class)->times(2)->create();
        $fofs = factory(Arubacao\Tests\Friendships\Models\User::class)->times(5)->create()->chunk(3);
        foreach ($recipients as $key => $recipient) {
            $sender->befriend($recipient);
            $recipient->acceptFriendRequest($sender);

            //add some friends to each recipient too
            foreach ($fofs[$key] as $fof) {
                $recipient->befriend($fof);
                $fof->acceptFriendRequest($recipient);
            }
        }

        $this->assertCount(2, $sender->getFriends());
        $this->assertCount(4, $recipients[0]->getFriends());
        $this->assertCount(3, $recipients[1]->getFriends());

        $this->assertCount(5, $sender->getFriendsOfFriends());

        $this->containsOnlyInstancesOf(Arubacao\Tests\Friendships\Models\User::class, $sender->getFriendsOfFriends());
    }
}