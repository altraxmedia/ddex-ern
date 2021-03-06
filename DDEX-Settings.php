<?php

/*
    A library to generate DDEX ERN XML files from pre-defined PHP classes

    Format: DDEX ERN 3.8.2 (Standart).

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
    SOFTWARE.

    This library licensed under the MIT License.

    Copyright (c) 2022 Georgy Akhmetov, Serhii Shmaida, Saveliy Safonov, Al-Trax Media Limited
*/

/*
    FTP/SFTP uploader settings
*/

class ConfirmationFile
{
	public $fileName = ''; # Name of batch completion file
	public $fileContents = '';
}

class UploaderSettings
{
	public $pasvFTP = true; # Use passive mode for FTP
	public $confirmationFile = null; # Last file in session
	public $port;
	public $login;
	public $password;
	public $serverIp;
}

class MCMSConfirmationFile extends ConfirmationFile
{
	public $fileName = 'batch.complete'; # Name of batch completion file
	public $fileContents = '';
}

class MCMSUploaderSettings extends UploaderSettings
{
	public function __construct ()
	{
		$this->confirmationFile = new MCMSConfirmationFile;
	}
}
