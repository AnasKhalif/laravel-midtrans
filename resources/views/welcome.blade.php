<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>IDonation</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <style>
        body {
            min-height: 75rem;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">IDonation</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item active">
                    <a class="nav-link" href="/donation">Donation <span class="sr-only">(current)</span></a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="jumbotron">
        <div class="container">
            <h1 class="display-4">IDonation</h1>
            <p class="lead">Platform donasi untuk saudara kita yang membutuhkan.</p>
        </div>
    </div>

    <div class="container">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Donor Name</th>
                    <th>Amount</th>
                    <th>Donation Type</th>
                    <th>Status</th>
                    <th style="text-align:center;"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($donations as $donation)
                    <tr>
                        <td><code>{{ $donation->id }}</code></td>
                        <td>{{ $donation->donor_name }}</td>
                        <td>Rp. {{ number_format($donation->amount) }},-</td>
                        <td>{{ ucwords(str_replace('_', ' ', $donation->donation_type)) }}</td>
                        <td>{{ ucfirst($donation->status) }}</td>
                        <td style="text-align:center;">
                            @if ($donation->status == 'pending')
                                <button class="btn btn-success btn-sm"
                                    onclick="snap.pay('{{ $donation->snap_token }}')">Complate Payment</button>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                {{ $donations->links() }}
            </tfoot>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.clientKey') }}"></script>
    <script>
        $("#donation_form").submit(function(event) {
            event.preventDefault();
            $.post("/api/donation", {
                _method: 'POST',
                _token: '{{ csrf_token() }}',
                donor_name: $("input#donor_name").val(),
                donor_email: $("input#donor_email").val(),
                donation_type: $("select#donation_type").val(),
                amount: $("input#amount").val(),
                note: $("textarea#note").val()
            }, function(data, status) {
                snap.pay(data.snap_token, {
                    // Optional
                    onSuccess: function(result) {
                        location.reload();
                    },
                    // Optional
                    onPending: function(result) {
                        location.reload();
                    },
                    // Optional
                    onError: function(result) {
                        location.reload();
                    }
                });
                return false;
            });
        });
    </script>
</body>

</html>
