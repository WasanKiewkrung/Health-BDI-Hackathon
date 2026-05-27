function confirmDelete(msg='ยืนยันการลบข้อมูลนี้?'){return confirm(msg)}
function printDoc(){window.print()}
function addLine(containerId, prefix){const c=document.getElementById(containerId);const row=document.createElement('div');row.className='row';row.innerHTML=`<div class="field"><input name="${prefix}_name[]" placeholder="ชื่อ"></div><div class="field"><input name="${prefix}_note[]" placeholder="รายละเอียด/ขนาด/หมายเหตุ"></div>`;c.appendChild(row)}
