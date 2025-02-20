<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;

class PostPolicy
{
   /**
    * Determine if the given user can update the post.
    *
    * @param  \App\Models\User  $user
    * @param  \App\Models\Post  $post
    * @return bool
    */
    public function update(User $user, Post $post) {
        return $user->id === $post->user_id;
    }

    /**
    * Determine if the given post can be deleted by the user.
    *
    * @param  \App\Models\User  $user
    * @param  \App\Models\Post  $post
    * @return bool
    */
    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }
}
