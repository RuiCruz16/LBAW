@extends('layouts.app')

@section('header')
    @include('partials.vertical_side_bar')
@endsection

@section('content')
    <div class="container py-5">
        <!-- Header Section -->
        <div class="row text-center mb-5">
            <div class="col">
                <h1 class="display-4 text-primary">Contact Us</h1>
                <p class="lead text-muted">Get in touch with us for any inquiries or support.</p>
            </div>
        </div>

        <!-- Contact Details -->
        <div class="row g-4">
            <!-- Office Location -->
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-light rounded h-100">
                    <div class="card-body">
                        <h3 class="card-title text-primary">Our Office</h3>
                        <p class="card-text mb-0">
                            Rua de Miguel Bombarda, 123, <br>
                            4050-352 Porto, Portugal
                        </p>
                    </div>
                </div>
            </div>

            <!-- Phone -->
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-light rounded h-100">
                    <div class="card-body">
                        <h3 class="card-title text-primary">Phone</h3>
                        <p class="card-text mb-0">
                            Call us at: +351 912 345 678 <br>
                            Available from 9 AM to 6 PM (Mon-Fri)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Email -->
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-light rounded h-100">
                    <div class="card-body">
                        <h3 class="card-title text-primary">Email</h3>
                        <p class="card-text mb-0">
                            For support or inquiries, email us at: <br>
                            <a href="mailto:contact@planora.com" class="text-decoration-none">contact@planora.com</a>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Social Media -->
            <div class="col-12 col-md-6">
                <div class="card shadow-sm border-light rounded h-100">
                    <div class="card-body">
                        <h3 class="card-title text-primary">Follow Us</h3>
                        <p class="card-text mb-0">
                            Stay connected with us on social media: <br>
                            <a href="#" class="text-decoration-none">Instagram</a> |
                            <a href="#" class="text-decoration-none">Twitter</a> |
                            <a href="#" class="text-decoration-none">LinkedIn</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-5">
            <div class="col text-center">
                <p class="text-muted">© 2024 Planora. All rights reserved.</p>
            </div>
        </div>
    </div>
@endsection
