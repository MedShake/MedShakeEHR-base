default:
	git ls-files | grep -v '^installer/' | grep -v '^tools/' | grep -v '^Makefile$$' > filelist.txt	
	zip -@ MedShakeEHR-base.zip < filelist.txt
	rm -f filelist.txt	

clean:
	rm -f MedShakeEHR-base.zip

sha256:
	git ls-files > filelist.txt	
	zip -@ MedShakeEHR-base.zip < filelist.txt
	tar -czf MedShakeEHR-base.tar.gz -T filelist.txt
	rm -f filelist.txt
	sha256sum MedShakeEHR-base.zip > SHA256SUMS
	sha256sum MedShakeEHR-base.tar.gz >> SHA256SUMS
	@echo "SHA256 (MedShakeEHR-base.zip): $$(sha256sum MedShakeEHR-base.zip | cut -d' ' -f1)"
	@echo "SHA256 (MedShakeEHR-base.tar.gz): $$(sha256sum MedShakeEHR-base.tar.gz | cut -d' ' -f1)"
	rm -f MedShakeEHR-base.zip MedShakeEHR-base.tar.gz 