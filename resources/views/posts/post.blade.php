@extends('layout')
@section('title', "post")
@section('content')
@auth
  {{ csrf_field() }}
<body>
  
  <main>
    <div class="container">
      <div class="row justify-content-center align-items-center vh-100 py-2">
        <div class="col-sm-10 col-md-8 col-lg-8 col-xl-7 col-xxl-6">
          <div class="card card-body rounded-3 p-4 p-sm-5"> 
            <form method="POST" action="{{ isset($post) ? route('post.update', ['id' => $post->id]) : route('post.store')}}" class="mt-4" enctype="multipart/form-data">
                @csrf
{{-- 
                @if($errors->any())
                <div class="col-12">
                  @foreach($errors->all() as $error)
                    <div class="alert alert-danger">{{$error}}</div>
                  @endforeach
                </div>
                @endif --}}
                
                  @isset($post)
                    <h3>Edit Post :</h3>
                  @else
                    <h3>Add Post :</h3>
                  @endisset
                  <br>
                  <div class="mb-3 position-relative">
                    <!-- post picture -->
                    <div class="col-sm-12 col-lg-12">
                      @isset($post['post_picture'])
                        <img class="card-img" src="/post-picture/{{$post['post_picture']}}" alt="Post">
                      @endisset

                      <br>
                      <input name="post_picture" type="file" class="form-control">
                    
                      @error('post_picture')
                      <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                      @enderror
                    </div>

                    <div class="mb-3 input-group-lg">
                      <label for="post" class="form-label">post :</label>
                      <textarea type="text" class="form-control" name="post" rows="5">{{ $post['post'] ?? old('post')}}</textarea>
                    
                      @error('post')
                      <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                      @enderror
                    </div>

                    <div class="mb-3 input-group-lg">
                      <label for="tag" class="form-label">hashtag :</label>
                      <input value="{{ $post['tag'] ?? old('tag')}}" type="text" class="form-control" name="tag">
                    
                      @error('tag')
                      <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                      @enderror
                    </div>
                  </div>
                  <br>
                  <button type="submit">
                    @isset($post)
                        <div  class="btn btn-primary">update</div>
                    @else
                        <div  class="btn btn-primary">add</div>
                    @endisset
                  </button>

                  <hr>
                  <div class=" text-center">
                    <a class="btn btn-link text-secondary btn-sm" type="submit" href="{{ route('profile', ['user_name' => Auth::user()->user_name]) }}">Back to Profile </a>
                  </div>

            </form>
          </div>
          <br>
        </div>
      </div>
    </div>
  </main>

    <!-- Modal create Feed photo START -->
    <div class="modal fade" id="feedActionPhoto" tabindex="-1" aria-labelledby="feedActionPhotoLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <!-- Modal feed header START -->
          <div class="modal-header">
            <h5 class="modal-title" id="feedActionPhotoLabel">Add post photo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <!-- Modal feed header END -->

            <!-- Modal feed body START -->
            <div class="modal-body">
            <!-- Add Feed -->
            <div class="d-flex mb-3">
              <!-- Avatar -->
              <div class="avatar avatar-xs me-2">
                <img class="avatar-img rounded-circle" src="assets/images/avatar/03.jpg" alt="">
              </div>
              <!-- Feed box  -->
              <form class="w-100">
                <textarea class="form-control pe-4 fs-3 lh-1 border-0" rows="2" placeholder="Share your thoughts..."></textarea>
              </form>
            </div>

            <!-- Dropzone photo START -->
            <div>
              <label class="form-label">Upload attachment</label>
              <div class="dropzone dropzone-default card shadow-none" data-dropzone='{"maxFiles":2}'>
                <div class="dz-message">
                  <i class="bi bi-images display-3"></i>
                  <p>Drag here or click to upload photo.</p>
                </div>
              </div>
            </div>
            <!-- Dropzone photo END -->

            </div>
            <!-- Modal feed body END -->

            <!-- Modal feed footer -->
            <div class="modal-footer ">
              <!-- Button -->
                <button type="button" class="btn btn-danger-soft me-2" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success-soft">Post</button>
            </div>
            <!-- Modal feed footer -->
        </div>
      </div>
    </div>
    <!-- Modal create Feed photo END -->

</body>
    @endauth
@endsection