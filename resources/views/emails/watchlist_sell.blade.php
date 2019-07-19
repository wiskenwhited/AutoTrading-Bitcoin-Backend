@extends('emails.layout.template')
@section('content')

    <div style="">
        <p>Hello, {{ $name}}</p>
        <p>
            You got this email because you set that you should after a sale is made.
        </p>
        <p>
            The CMB just made a sale of
        </p>
        <p></p>
        <table style="border: 1px solid #d0d0d0;color: #677d9d; font-family: Roboto, 'Helvetica Neue', sans; width: 100%;border-collapse: collapse;overflow-x: scroll;"
               border="1">
            <tr style="background-color: #ebf0f5;font-size: 13px;font-weight: 400;line-height: 34px;padding: 0 10px;border: 1px solid #d0d0d0;">
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Coin
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Quantity
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Unit Price
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Total Price
                </td>
                <td style="border: 1px solid #d0d0d0;background-color: #ebf0f5;text-align: left; height: 20px; padding-left: 5px;">
                    Price Bought
                </td>
            </tr>
            <tr style="font-size: 12px;font-weight: 400;line-height: 34px;padding: 0 10px;border: 1px solid #d0d0d0;">
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $watchlist->coin_relation ? $watchlist->coin_relation->symbol : $watchlist->coin }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $trade->quantity }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $trade->price_per_unit }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $trade->price }}</td>
                <td style="border: 1px solid #d0d0d0;text-align: left; height: 20px; padding-left: 5px;">{{ $trade->originalTrade ? $trade->originalTrade->price_per_unit : '-'}}</td>
            </tr>
        </table>
        <p></p>
        <p>
            Thank you for trading with the XchangeRate CMB
        </p>
    </div>

@endsection