# Import Template

Neu ban muon gui du lieu theo kieu `1 file = 1 chuong`, hay dat file theo cau truc:

```text
import/
  ten-truyen/
    cover.jpg
    chuong-001-mo-dau.txt
    chuong-002-vet-sang.txt
    chuong-003-...
```

Quy tac nen dung:

- Moi file la 1 chuong.
- Ten file nen theo mau: `chuong-001-ten-chuong.txt`
- Moi file deu lap lai thong tin truyen o phan dau de minh map dung vao he `Truyen / Chuong`.
- Dong `Số chương:` la so cua chuong hien tai trong file do, vi du chuong 1 thi ghi `Số chương: 1`.
- Importer uu tien doc so chuong tu `Slug chương` hoac ten file. Neu 2 cho nay la `chuong-001` ma dong `Số chương:` ghi `31`, importer van se hieu day la chuong 1 va canh bao de ban sua lai.
- Dong `Thể loại:` nen ghi tren 1 dong, cac the loai cach nhau bang dau phay.
  Vi du: `Thể loại: Nguyên sang, Đam mỹ, Hiện đại, HE, Tình cảm, Chủ công`
- Neu co anh bia, de cung folder voi cac file chuong.
- File `.txt` rong se duoc bo qua khi import.
- Folder truyen moi ma tat ca file `.txt` deu rong se duoc auto sync bo qua cho toi khi ban bat dau dien noi dung that.

Ban co the copy mau trong `chapter-template.txt` roi doi ten file theo tung chuong.

Sau khi file da san sang, vao WordPress admin -> `Tools` -> `Import truyện`, nhap ten folder vi du `mot-vien-keo`, roi bam import.

Neu dang chay local tren `localhost`, theme se tu `auto sync` khi file trong `import/` thay doi. Thuong chi can save file roi refresh web 1 lan la du lieu moi se tu len site.

Khi gui cho minh, chi can noi duong dan folder, vi du:

```text
/Users/ho.vuong.tuong.vy/Documents/comeout-with-me/import/anh-sang-cuoi-duong-ham
```
