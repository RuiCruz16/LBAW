@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container py-5">
        <!-- Introduction Section -->
        <div class="row text-center mb-5">
            <div class="col">
                <h1 class="display-4 text-primary">About Us</h1>
                <p class="lead text-muted">Learn more about our mission, vision, and the founders behind Planora.</p>
            </div>
        </div>

        <!-- Mission and Vision Section -->
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-light rounded">
                    <div class="card-body">
                        <h3 class="card-title text-primary">Our Mission</h3>
                        <p class="card-text">
                            Our mission is to empower teams worldwide by providing a platform for collaboration, transparency, and efficient project management. We aim to revolutionize the way teams manage their work, ensuring seamless communication and task tracking.
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-6 mb-4">
                <div class="card shadow-sm border-light rounded">
                    <div class="card-body">
                        <h3 class="card-title text-primary">Our Vision</h3>
                        <p class="card-text">
                            We envision a world where teams collaborate seamlessly and projects are completed with ease and efficiency, leading to global success. Our platform is designed to help individuals and teams stay organized, productive, and connected.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div class="row my-5 text-center">
            <div class="col">
                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#featuresModal">
                    Wanna Know More? Learn About Our Main Features
                </button>
            </div>
        </div>

        @include('partials.features_popup')

        <!-- Founders Section -->
        <div class="row mt-5">
            <div class="col-12 text-center mb-4">
                <h2 class="text-primary">Meet the Founders</h2>
                <p class="lead text-muted">The talented minds behind Planora.</p>
            </div>

            @foreach ([
                ['name' => 'Diogo Ferreira', 'role' => 'Co-Founder', 'bio' => 'Diogo is a passionate tech enthusiast with a natural curiosity for problem-solving. As a co-founder, he brings a creative and strategic mindset to the team. Diogo is always eager to explore new solutions to help teams work smarter, not harder. Outside of tech, he enjoys adventure sports and discovering new challenges.'],
                ['name' => 'Gonçalo Marques', 'role' => 'Co-Founder', 'bio' => 'Gonçalo is a curious thinker and a problem-solver. With a knack for understanding complex systems, he’s always looking for ways to optimize processes and bring innovative ideas to life. When he’s not coding, Gonçalo enjoys reading philosophy and diving into the deep questions about how things work.'],
                ['name' => 'Lucas Greco', 'role' => 'Co-Founder', 'bio' => 'Lucas is a developer with a passion for building scalable solutions. With a background in both development and design, Lucas is always focused on crafting user-friendly products that combine technical excellence and intuitive design. Outside of work, he enjoys photography and exploring the outdoors.'],
                ['name' => 'Rui Cruz', 'role' => 'Co-Founder', 'bio' => 'Rui is passionate about user-centered design and product development. With an eye for detail, he ensures that every aspect of Planora is intuitive and meets the needs of our users. Rui believes in creating technology that makes life simpler and more enjoyable. In his free time, he enjoys coffee, reading, and traveling.']
            ] as $founder)
                <div class="col-12 col-sm-6 col-md-3 mb-4">
                    <div class="card shadow-sm border-light rounded">
                        <div class="card-body text-center">
                            <h5 class="card-title text-primary">{{ $founder['name'] }}</h5>
                            <p class="card-text">{{ $founder['role'] }}</p>
                            <p class="text-muted">{{ $founder['bio'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Footer -->
        <div class="row mt-5">
            <div class="col text-center">
                <p class="text-muted">© 2024 Planora. All rights reserved.</p>
            </div>
        </div>
    </div>
@endsection
