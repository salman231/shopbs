function printBarcode()  {
    var printWindow = window.open("", "PRINT", "height=400,width=600");
    printWindow.document.write(
        "<html><head><title>Barcode Slip</title></head><style>td{padding:10px;vertical-align:top;}</style><body>"
    );
    printWindow.document.write(document.getElementById("printable").innerHTML);
    printWindow.document.write("</body></html>");
    printWindow.print();
}

window.addEventListener('load', function () {
    var printWindow = window.open("", "PRINT", "height=400,width=600");
    printWindow.document.write(
        "<html><head><title>Barcode Slip</title></head><style>td{padding:10px;vertical-align:top;}</style><body>"
    );
    printWindow.document.write(document.getElementById("printable").innerHTML);
    printWindow.document.write("</body></html>");
    printWindow.print();
});