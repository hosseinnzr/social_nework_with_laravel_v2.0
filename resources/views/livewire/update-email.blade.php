<div>

    <!-- Button -->
    <style>
        .loader-container {
            margin-top: 4px;
            width: 100%;
            height: 4px;
            background-color: #e0e0e0;
            position: relative;
            overflow: hidden;
            border-radius: 0px 0px 5px 5px;
        }

        .loader-line {
            width: 10%;
            height: 100%;
            background-color: #0f6fec;
            position: absolute;
            top: 0;
            left: 0;
            animation: moveLine 1s linear infinite;
        }

        @keyframes moveLine {
            0% {
                left: 0;
            }
            100% {
                left: 100%;
            }
        }
    </style>

    <div style=" font-size: 0.8em;">
        @if($errors->any())
            {!! implode('', $errors->all('<div class="alert alert-danger alert-dismissible fade show" role="alert">:message</div>')) !!}
        @endif

        @if(Session::get('error') && Session::get('error') != null)
            <div class="alert alert-secondary  alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
            </div>
            @php
            Session::put('error', null)
            @endphp
        @endif

        @if(Session::get('success') && Session::get('success') != null)
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ Session::get('success') }}
            </div>

            @php
            Session::put('success', null)
            @endphp
        @endif


    </div>

    @if ($send_code == false)
        <!-- Title -->
        <div class="col-sm-12 col-lg-12 mb-3">
            <label class="form-label">Current email : {{Auth::user()->email}}</label>
        </div>

        <form wire:submit="sendcode()" method="POST" class="row g-3">
            @csrf
            
            <div class="col-sm-12 mb-2">
                <label class="form-label">New email :</label>
                <input wire:model="email" type="text" class="form-control" placeholder="Enter new email" value="">
            
                @error('email')
                <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                @enderror
            </div>

            <div class="col-2 text-end">
                <div class="d-grid ">
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary w-100"> 
                            next
                            <br>            
                        </button>
                    </div>
                    <div wire:loading.flex>
                        <button type="submit" style="padding: 8px 0px 0px 0px" class="btn btn-primary w-100"> 
                            next
                            <br>  
                            <div class="loader-container">
                                <div class="loader-line"></div>
                            </div>          
                        </button>
                    </div>
                </div>
            </div>

        </form>
    @else
        <!-- Title -->
        <div class="col-sm-12 col-lg-12 mb-3">
            <label class="form-label">Current email : {{Auth::user()->email}}</label>
        </div>
        <div class="col-sm-12 col-lg-12 mb-3">
            <label class="form-label">New email : {{$email}}</label>
        </div>

        <form wire:submit="emailUpdate()" method="POST" class="row g-3">
            @csrf
            
            <div class="col-sm-12">
                <label class="form-label">verify code:</label>
                <input wire:model="verify_code" type="text" class="form-control" placeholder="Enter verify code" value="">
            
                @error('verify_code')
                <p class="text-red-500 text-xs mt-1">{{$message}}</p>
                @enderror
            </div>

            <div class="col-3 text-end">
                <div class="d-grid">
                    <div wire:loading.remove>
                        <button type="submit" class="btn btn-primary w-100"> 
                            update email
                            <br>            
                        </button>
                    </div>
                    <div wire:loading.flex>
                        <button type="submit" style="padding: 8px 0px 0px 0px" class="btn btn-primary w-100"> 
                            update email
                            <br>  
                            <div class="loader-container">
                                <div class="loader-line"></div>
                            </div>          
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endif

</div>
