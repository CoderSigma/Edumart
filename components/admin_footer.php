<footer class="footer bg-dark text-white fixed-bottom">
    <div class="container text-center">
        Copyright &copy; CoderSigma | All rights reserved.
    </div>
</footer>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.0/xlsx.full.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/TableExport/5.2.0/js/tableexport.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
<script>
document.getElementById('exportExcel').addEventListener('click', function () {
    const table = document.querySelector('table');
    const fileTitle = document.getElementById('tit').innerText || 'default_title'; // Get the title from the element with id="tit"

    if (table) {
        const exportInstance = new TableExport(table, {
            headers: true,
            footers: true,
            formats: ['xlsx'], 
            filename: fileTitle.replace(/\s+/g, '_').toLowerCase(), // Use the extracted title as filename
            bootstrap: true,
            exportButtons: false
        });
        const exportData = exportInstance.getExportData();
        const xlsxData = exportData[Object.keys(exportData)[0]].xlsx;
        exportInstance.export2file(xlsxData.data, xlsxData.mimeType, xlsxData.filename, xlsxData.fileExtension);
    } else {
        console.error("Table not found for Excel export.");
    }
});

document.getElementById('exportPDF').addEventListener('click', function () {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    const fileTitle = document.getElementById('tit').innerText || 'default_title'; 

    doc.text(fileTitle, 14, 10); 
    doc.autoTable({ 
        html: 'table', 
        startY: 20, 
        theme: 'grid', 
        headStyles: { fillColor: [33, 150, 243] }
    });
    doc.save(`${fileTitle.replace(/\s+/g, '_').toLowerCase()}.pdf`);
});
</script>